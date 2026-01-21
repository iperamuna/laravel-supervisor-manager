<?php

namespace Iperamuna\LaravelSupervisorManager;

use Illuminate\Support\ServiceProvider;

use function Laravel\Prompts\info;

class SupervisorManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/supervisor-manager.php',
            'supervisor-manager'
        );

        $this->app->singleton(Services\SupervisorApiService::class, function ($app) {
            return new Services\SupervisorApiService;
        });

        $this->app->singleton(Services\SupervisorConfigService::class, function ($app) {
            return new Services\SupervisorConfigService;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/supervisor-manager.php' => config_path('supervisor-manager.php'),
        ], 'supervisor-manager-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/theme.css' =>
                public_path('vendor/supervisor-manager/theme.css'),
        ], 'supervisor-manager-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\SupervisorManagerInstallCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'supervisor-manager');

        \Livewire\Livewire::component('supervisor-manager::supervisor-status', Livewire\SupervisorStatus::class);
        \Livewire\Livewire::component('supervisor-manager::process-list', Livewire\ProcessList::class);
        \Livewire\Livewire::component('supervisor-manager::configuration-list', Livewire\ConfigurationList::class);
        \Livewire\Livewire::component('supervisor-manager::log-viewer', Livewire\LogViewer::class);
        \Livewire\Livewire::component('supervisor-manager::redis-details', Livewire\RedisDetails::class);
        \Livewire\Livewire::component('supervisor-manager::redis-content', Livewire\RedisContent::class);

        $userModel = config('supervisor-manager.user_model');
        if ($userModel && class_exists($userModel) && !in_array(\Filament\Models\Contracts\FilamentUser::class, class_implements($userModel))) {
            info('Supervisor Manager: The configured user model does not implement the FilamentUser interface.');
        }
    }
}
