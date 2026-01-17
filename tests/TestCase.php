<?php

namespace Iperamuna\SupervisorManager\Tests;

use Iperamuna\SupervisorManager\SupervisorManagerServiceProvider;
use Iperamuna\SupervisorManager\SupervisorPanelProvider;
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
