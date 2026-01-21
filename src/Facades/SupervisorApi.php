<?php

namespace Iperamuna\LaravelSupervisorManager\Facades;

use Illuminate\Support\Facades\Facade;
use Iperamuna\LaravelSupervisorManager\Services\SupervisorApiService;

/**
 * @method static array getAllProcessInfo()
 * @method static array getProcessInfo(string $name)
 * @method static bool startProcess(string $name, bool $wait = true)
 * @method static bool stopProcess(string $name, bool $wait = true)
 * @method static bool restartProcess(string $name, bool $wait = true)
 * @method static array startAllProcesses(bool $wait = true)
 * @method static array stopAllProcesses(bool $wait = true)
 * @method static array getState()
 * @method static string readProcessStdoutLog(string $name, int $offset, int $length)
 * @method static string readProcessStderrLog(string $name, int $offset, int $length)
 * @method static bool clearProcessLogs(string $name)
 *
 * @see \Iperamuna\LaravelSupervisorManager\Services\SupervisorApiService
 */
class SupervisorApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupervisorApiService::class;
    }
}
