<?php

namespace Iperamuna\LaravelSupervisorManager\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class SupervisorManagerInstallCommand extends Command
{
    public $signature = 'supervisor-manager:install';

    public $description = 'Install the Supervisor Manager package';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'supervisor-manager-config',
        ]);

        $panelUrl = text(
            label: 'What is the Supervisor Panel URL?',
            default: 'supervisor',
            hint: 'The URL path where the dashboard will be accessible.'
        );

        $url = text(
            label: 'What is the Supervisor Connection URL?',
            default: 'http://127.0.0.1',
            hint: 'The URL of the Supervisor XML-RPC server.'
        );

        $port = text(
            label: 'What is the Supervisor Connection Port?',
            default: '9125',
            hint: 'The port of the Supervisor XML-RPC server.'
        );

        $username = text(
            label: 'What is the Supervisor Connection Username?',
            default: 'user',
        );

        $password = text(
            label: 'What is the Supervisor Connection Password?',
            default: '123',
        );

        $this->updateEnv([
            'SUPERVISOR_PANEL_PATH' => $panelUrl,
            'SUPERVISOR_URL' => $url,
            'SUPERVISOR_PORT' => $port,
            'SUPERVISOR_USERNAME' => $username,
            'SUPERVISOR_PASSWORD' => $password,
        ]);

        $this->configureTailwind();

        info('Supervisor Manager installed successfully.');

        if (\Laravel\Prompts\confirm(label: 'Do you want to create a Supervisor Admin user?', default: true)) {
            $userModel = config('supervisor-manager.user_model');
            if ($userModel && class_exists($userModel)) {
                $name = text(label: 'Name', required: true);
                $email = text(label: 'Email', required: true, validate: fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL) ? null : 'Invalid email');
                $UserPassword = \Laravel\Prompts\password(label: 'Password', required: true);

                try {
                    $user = $userModel::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => \Illuminate\Support\Facades\Hash::make($UserPassword),
                    ]);
                    info("User '{$user->email}' created successfully.");
                } catch (\Exception $e) {
                    \Laravel\Prompts\error("Failed to create user: " . $e->getMessage());
                }
            } else {
                \Laravel\Prompts\warning("Configured User model ($userModel) not found. Skipping user creation.");
            }
        }

        \Laravel\Prompts\warning("IMPORTANT: Ensure your User model implements the FilamentUser interface and defines the 'canAccessPanel' method.");
        info("
Example:

use Filament\\Models\\Contracts\\FilamentUser;
use Filament\\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel \$panel): bool
    {
        return str_ends_with(\$this->email, '@yourdomain.com');
    }
}
");

        // Secure Copy Setup Guide
        $this->newLine(2);
        \Laravel\Prompts\info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        \Laravel\Prompts\info('📦 SECURE COPY SETUP (Required for Production)');
        \Laravel\Prompts\info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Detect system user
        $systemUser = 'www-data'; // default
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $systemUser = posix_getpwuid(posix_geteuid())['name'] ?? 'www-data';
        }
        \Laravel\Prompts\info("Detected system user: {$systemUser}");
        $this->newLine();

        \Laravel\Prompts\info('To securely deploy supervisor configs, run the setup script:');
        $this->newLine();
        $this->line('  <fg=green>cd vendor/iperamuna/supervisor-manager/scripts</>');
        $this->line('  <fg=green>bash setup-secure-copy.sh</>');
        $this->newLine();

        \Laravel\Prompts\info('Or manually:');
        $this->newLine();
        $this->line('  <fg=yellow>1. Install the copy script:</>');
        $this->line('     sudo cp vendor/iperamuna/supervisor-manager/scripts/supervisor-copy /usr/local/bin/');
        $this->line('     sudo chmod +x /usr/local/bin/supervisor-copy');
        $this->newLine();
        $this->line('  <fg=yellow>2. Configure sudoers (sudo visudo):</>');
        $this->line("     {$systemUser} ALL=(root) NOPASSWD: /usr/local/bin/supervisor-copy *");
        $this->newLine();
        $this->line('  <fg=yellow>3. Update your .env:</>');
        $this->line("     SUPERVISOR_SYSTEM_USER={$systemUser}");
        $this->line('     SUPERVISOR_USE_SECURE_COPY=true');
        $this->newLine();

        \Laravel\Prompts\info('For detailed instructions, see: README.md');
        \Laravel\Prompts\info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return self::SUCCESS;
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // encapsulate string values with quotes if they contain spaces
            if (preg_match('/\s/', $value)) {
                $value = '"' . $value . '"';
            }

            if (str_contains($envContent, $key . '=')) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    protected function configureTailwind(): void
    {
        $cssPath = resource_path('css/app.css');

        if (!file_exists($cssPath)) {
            return;
        }

        $css = file_get_contents($cssPath);
        // We use the vendor path as this command is intended for the distributed package users
        $sourceLine = "@source '../../vendor/iperamuna/supervisor-manager/resources/views/**/*.blade.php';";

        if (!str_contains($css, 'iperamuna/supervisor-manager')) {
            file_put_contents($cssPath, $css . "\n" . $sourceLine);
            info('Added package views to tailwind source.');
        }
    }
}
