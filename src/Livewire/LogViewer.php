<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Filament\Notifications\Notification;
use Livewire\Component;

class LogViewer extends Component
{
    public string $process;

    public string $type = 'stdout'; // stdout or stderr

    public string $content = '';

    public int $offset = 0;

    public int $length = 10000;

    public function mount(string $process, string $type = 'stdout')
    {
        $this->process = $process;
        $this->type = $type;
        $this->refreshLogs();
    }

    public function refreshLogs()
    {
        try {
            $api = app(\Iperamuna\LaravelSupervisorManager\Services\SupervisorApiService::class);
            if ($this->type === 'stderr') {
                $result = $api->tailProcessStderrLog($this->process, $this->offset, $this->length);
            } else {
                $result = $api->tailProcessStdoutLog($this->process, $this->offset, $this->length);
            }

            // Result is [string log, int offset, bool overflow]
            $this->content = $result[0] ?? '';

        } catch (\Exception $e) {
            $this->content = 'Failed to load logs: '.$e->getMessage();
        }
    }

    public function clearLogs()
    {
        try {
            $api = app(\Iperamuna\LaravelSupervisorManager\Services\SupervisorApiService::class);
            if ($api->clearProcessLogs($this->process)) {
                $this->content = '';
                Notification::make()->title('Logs cleared')->success()->send();
                $this->refreshLogs();
            }
        } catch (\Exception $e) {
            Notification::make()->title('Failed to clear logs')->body($e->getMessage())->danger()->send();
        }
    }

    public function render()
    {
        return view('supervisor-manager::livewire.log-viewer');
    }
}
