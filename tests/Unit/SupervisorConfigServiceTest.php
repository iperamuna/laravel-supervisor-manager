<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Iperamuna\SupervisorManager\Services\SupervisorConfigService;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().'/supervisor_mgr_test_'.uniqid();
    File::makeDirectory($this->tempDir);
    Config::set('supervisor-manager.supervisors_dir', $this->tempDir);
    Config::set('supervisor-manager.conf_path', $this->tempDir.'/system'); // Mock system path too
    File::makeDirectory($this->tempDir.'/system');
});

afterEach(function () {
    File::deleteDirectory($this->tempDir);
});

it('can parse a simple supervisor config file', function () {
    $content = <<<'INI'
[program:test-worker]
process_name=%(program_name)s_%(process_num)02d
command=php artisan horizon
autostart=true
autorestart=true
user=forge
numprocs=8
redirect_stderr=true
stdout_logfile=/home/forge/app.com/storage/logs/horizon.log
INI;

    $filePath = $this->tempDir.'/test-worker.conf';
    file_put_contents($filePath, $content);

    $service = new SupervisorConfigService;
    // Re-instantiate or assume constructor reads config at runtime?
    // The constructor reads config: $this->localPath = config(...).
    // So we must instantiation AFTER setting config in beforeEach.

    $config = $service->getConfig('test-worker.conf');

    expect($config)->not->toBeNull()
        ->and($config['program'])->toBe('test-worker')
        ->and($config['command'])->toBe('php artisan horizon')
        ->and($config['numprocs'])->toBe('8');
});

it('can list configurations with correct status', function () {
    $content = "[program:worker]\ncommand=php artisan work";
    file_put_contents($this->tempDir.'/worker.conf', $content);

    $service = new SupervisorConfigService;
    $configs = $service->listConfigs();

    expect($configs)->toHaveCount(1)
        ->and($configs[0]['name'])->toBe('worker')
        ->and($configs[0]['status'])->toBe('new');
    // Status 'new' because it doesn't exist in the mocked system path ($this->tempDir . '/system') yet.
});
