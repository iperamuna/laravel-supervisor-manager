<?php

namespace Iperamuna\SupervisorManager\Facades;

use Illuminate\Support\Facades\Facade;
use Iperamuna\SupervisorManager\Services\SupervisorApiService;

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
 * @see \Iperamuna\SupervisorManager\Services\SupervisorApiService
 */
class SupervisorApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupervisorApiService::class;
    }
}
