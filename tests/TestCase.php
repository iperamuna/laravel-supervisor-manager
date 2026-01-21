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
            SupervisorManagerServiceProvider::class,
            SupervisorPanelProvider::class,
        ];
    }
}
