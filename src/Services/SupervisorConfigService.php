<?php

namespace Iperamuna\SupervisorManager\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SupervisorConfigService
{
    protected string $systemPath;

    protected string $localPath;

    public function __construct()
    {
        $this->systemPath = config('supervisor-manager.conf_path');
        $this->localPath = config('supervisor-manager.supervisors_dir');
    }

    public function listConfigs(): array
    {
        // Ensure local directory exists
        if (!File::exists($this->localPath)) {
            File::makeDirectory($this->localPath, 0755, true);
        }

        // We list configs from LOCAL path as the primary source of truth for management
        $localFiles = File::files($this->localPath);

        // Also check system path for files that might be orphans or just synced
        $systemFiles = File::exists($this->systemPath) ? File::files($this->systemPath) : [];
        $systemFileNames = array_map(fn($f) => $f->getFilename(), $systemFiles);

        $configs = [];

        foreach ($localFiles as $file) {
            if ($file->getExtension() === 'conf') {
                $filename = $file->getFilename();
                $data = $this->parseConfigFile($file->getPathname());

                if ($data) {
                    $item = array_merge([
                        'filename' => $filename,
                        'full_path' => $file->getPathname(),
                        'name' => $file->getFilenameWithoutExtension(),
                    ], $data);

                    // Check status against system
                    if (!in_array($filename, $systemFileNames)) {
                        $item['status'] = 'new'; // Exists locally, not in system
                    } else {
                        // Compare content
                        $localContent = File::get($file->getPathname());
                        $systemContent = File::get($this->systemPath . '/' . $filename);

                        // Simple content comparison (could be improved by normalizing whitespace/lines)
                        if (trim($localContent) === trim($systemContent)) {
                            $item['status'] = 'synced';
                        } else {
                            $item['status'] = 'modified'; // Exists in both but content differs
                        }
                    }

                    $configs[] = $item;
                }
            }
        }

        // Process System-only files (Orphans)
        foreach ($systemFiles as $file) {
            $filename = $file->getFilename();
            // Skip if already processed (exists locally)
            if (File::exists($this->localPath . '/' . $filename)) {
                continue;
            }

            if ($file->getExtension() === 'conf') {
                $data = $this->parseConfigFile($file->getPathname());
                if ($data) {
                    $configs[] = array_merge([
                        'filename' => $filename,
                        'full_path' => $file->getPathname(),
                        'name' => $file->getFilenameWithoutExtension(),
                        'status' => 'system-only', // Exists in system, not locally
                    ], $data);
                }
            }
        }

        return $configs;
    }

    // Reads config from LOCAL path for editing, fallback to SYSTEM if orphan
    public function getConfig(string $filename): ?array
    {
        $path = $this->localPath . '/' . $filename;
        if (!File::exists($path)) {
            // Check system path (orphan)
            $path = $this->systemPath . '/' . $filename;
            if (!File::exists($path)) {
                return null;
            }
        }

        return $this->parseConfigFile($path);
    }

    // Saves to LOCAL path
    public function saveConfig(string $filename, array $data): bool
    {
        $content = $this->buildConfigContent($data);

        // Ensure directory exists
        if (!File::exists($this->localPath)) {
            File::makeDirectory($this->localPath, 0755, true);
        }

        return File::put($this->localPath . '/' . $filename, $content) !== false;
    }

    // Syncs a specific file from Local to System using secure copy script
    public function syncToSystem(string $filename): bool
    {
        $localFile = $this->localPath . '/' . $filename;

        if (!File::exists($localFile)) {
            throw new \RuntimeException("Local configuration file not found: {$localFile}");
        }

        $useSecureCopy = config('supervisor-manager.use_secure_copy', true);

        if ($useSecureCopy) {
            return $this->secureCopyToSystem($localFile);
        } else {
            return $this->directCopyToSystem($localFile, $filename);
        }
    }

    /**
     * Copy file to system using secure sudo script (recommended for production)
     */
    protected function secureCopyToSystem(string $localFile): bool
    {
        $copyScriptPath = config('supervisor-manager.copy_script_path', '/usr/local/bin/supervisor-copy');

        if (!file_exists($copyScriptPath)) {
            throw new \RuntimeException(
                "Secure copy script not found at: {$copyScriptPath}\n" .
                "Please run the installation guide to set up the copy script."
            );
        }

        try {
            $process = Process::fromShellCommandline(
                'sudo ' . escapeshellarg($copyScriptPath) . ' ' . escapeshellarg($localFile)
            );

            $process->setTimeout(30);
            $process->mustRun();

            return true;
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException(
                "Failed to copy configuration using secure script:\n" .
                $exception->getMessage()
            );
        }
    }

    /**
     * Direct copy to system directory (legacy mode, requires write permissions)
     */
    protected function directCopyToSystem(string $localFile, string $filename): bool
    {
        $systemFile = $this->systemPath . '/' . $filename;

        if (!File::exists($this->systemPath)) {
            File::makeDirectory($this->systemPath, 0755, true);
        }

        if (!File::copy($localFile, $systemFile)) {
            throw new \RuntimeException("Failed to copy file to system directory: {$systemFile}");
        }

        // Run supervisorctl commands
        $this->deployChanges();

        return true;
    }

    // Runs supervisor update sequence
    public function deployChanges(): array
    {
        // Executes supervisorctl update, reread, etc.
        // Assuming we are on the same system and have permission
        $output = [];
        $returnVar = 0;

        exec('supervisorctl reread', $output, $returnVar);
        exec('supervisorctl update', $output, $returnVar);

        // Optionally restart implies finding changed groups, but usually update handles adding/removing.
        // If the user wants to ensure restart of existing modified groups that update doesn't trigger automatically (update only triggers if config changed significantly enough for supervisor to notice)
        // For now, let's stick to reread + update which is standard for config changes.

        return [
            'exit_code' => $returnVar,
            'output' => implode("\n", $output),
        ];
    }

    protected function parseConfigFile(string $path): ?array
    {
        $content = $this->parseIniFile($path);
        foreach ($content as $section => $values) {
            if (str_starts_with($section, 'program:')) {
                $values['section_name'] = $section;
                $values['program'] = substr($section, 8);

                return $values;
            }
        }

        return null;
    }

    protected function buildConfigContent(array $data): string
    {
        $sectionName = 'program:' . ($data['program'] ?? 'unknown');

        $lines = [];
        $lines[] = "[$sectionName]";

        $allowedKeys = [
            'process_name',
            'command',
            'autostart',
            'autorestart',
            'stopasgroup',
            'killasgroup',
            'user',
            'numprocs',
            'redirect_stderr',
            'stdout_logfile',
            'stderr_logfile',
            'startsecs',
            'stopwaitsecs',
            'environment',
            'directory',
        ];

        // Format specific fields
        foreach ($allowedKeys as $key) {
            if (isset($data[$key]) && $data[$key] !== null && $data[$key] !== '') {
                $value = $data[$key];
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $lines[] = "$key=$value";
            }
        }

        return implode("\n", $lines);
    }

    protected function parseIniFile(string $path): array
    {
        // parse_ini_file with sections
        return parse_ini_file($path, true, INI_SCANNER_RAW) ?: [];
    }
}
