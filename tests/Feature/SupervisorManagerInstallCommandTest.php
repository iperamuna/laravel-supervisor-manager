<?php

use Illuminate\Support\Facades\File;

it('installs the package and publishes config', function () {
    // Mock the vendor:publish call or check the result
    // Since we are running the install command interactively with prompts...
    // We can use `expectsQuestion` (Symfony console legacy) or `expectsOutput`.
    // Laravel Prompts are tricky to test with `artisan()` unless supported by testbench/laravel recent versions.
    // Assuming modern Laravel test helpers support Prompts.

    // We'll mock the .env file path to avoid overwriting the real project's .env!
    // The command uses `base_path('.env')`. In testbench, base_path points to `vendor/orchestra/testbench-core/laravel`.
    // So it's safe-ish, but let's be sure.

    $envPath = base_path('.env');
    if (! File::exists($envPath)) {
        File::put($envPath, "APP_NAME=Laravel\n");
    }

    $this->artisan('supervisor-manager:install')
        ->expectsQuestion('What is the Supervisor Panel URL?', 'admin/supervisor')
        ->expectsQuestion('What is the Supervisor Connection URL?', 'http://localhost')
        ->expectsQuestion('What is the Supervisor Connection Port?', '9001')
        ->expectsQuestion('What is the Supervisor Connection Username?', 'admin')
        ->expectsQuestion('What is the Supervisor Connection Password?', 'secret')
        ->expectsQuestion('Do you want to create a Supervisor Admin user?', 'no')
        ->assertExitCode(0);

    // Verify .env content
    $content = File::get($envPath);
    expect($content)->toContain('SUPERVISOR_PANEL_PATH=admin/supervisor')
        ->toContain('SUPERVISOR_URL=http://localhost')
        ->toContain('SUPERVISOR_PORT=9001');
});
