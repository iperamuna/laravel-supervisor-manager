<?php

namespace Iperamuna\LaravelSupervisorManager\Tests;

use Iperamuna\LaravelSupervisorManager\SupervisorManagerServiceProvider;
use Iperamuna\LaravelSupervisorManager\SupervisorPanelProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            SupervisorManagerServiceProvider::class,
            SupervisorPanelProvider::class,
        ];
    }
}
