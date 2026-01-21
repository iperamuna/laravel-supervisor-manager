<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Iperamuna\LaravelSupervisorManager\Facades\SupervisorApi;
use Livewire\Component;

class ProcessList extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function getProcessesProperty(): array
    {
        try {
            $processes = SupervisorApi::getAllProcessInfo();

            // Enhance with local system info if applicable
            // This assumes the supervisor is running on the same machine as this PHP script
            // and we have shell access.
            foreach ($processes as &$process) {
                if ($process['state'] == 20 && ! empty($process['pid'])) { // RUNNING
                    $this->enhanceProcessInfo($process);
                }
            }

            return $processes;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function enhanceProcessInfo(array &$process): void
    {
        try {
            $pid = (int) $process['pid'];

            // Get user and command using ps
            // 'user' might be different on some systems, generally 'user' or 'uname'
            // 'command' or 'args' gives the full command
            $output = [];
            // Mac/Linux compliant ps command
            exec("ps -p $pid -o user=,command=", $output);

            if (! empty($output)) {
                $line = trim($output[0]);
                // format is "USER   COMMAND..."
                // Split by whitespace with limit 2
                $parts = preg_split('/\s+/', $line, 2);

                if (count($parts) >= 1) {
                    $process['user'] = $parts[0];
                }
                if (count($parts) >= 2) {
                    $process['command'] = $parts[1];
                }
            }
        } catch (\Exception $e) {
            // checking failed, ignore
        }
    }

    public function restartProcessAction(): Action
    {
        return Action::make('restartProcess')
            ->label('Restart')
            ->icon('heroicon-m-arrow-path')
            ->color('warning')
            ->iconButton()
            ->size('xs')
            ->requiresConfirmation()
            ->modalHeading('Restart Process')
            ->modalDescription('Are you sure you want to restart this process?')
            ->modalSubmitActionLabel('Yes, Restart')
            ->action(function (array $arguments) {
                $name = $arguments['group'].':'.$arguments['name'];
                try {
                    // try restart (stop then start)
                    if (SupervisorApi::restartProcess($name)) {
                        Notification::make()->title("Process $name restarted.")->success()->send();
                    } else {
                        throw new \Exception('Supervisor returned false');
                    }
                } catch (\Exception $e) {
                    Notification::make()->title("Failed to restart $name.")->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function viewStdoutAction(): Action
    {
        return Action::make('viewStdout')
            ->label('LOGS')
            ->modalHeading('Process Standard Output')
            ->modalContent(fn (array $arguments) => view('supervisor-manager::components.log-viewer-modal', [
                'process' => $arguments['group'].':'.$arguments['name'],
                'type' => 'stdout',
            ]))
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }

    public function viewStderrAction(): Action
    {
        return Action::make('viewStderr')
            ->label('ERR')
            ->modalHeading('Process Standard Error')
            ->modalContent(fn (array $arguments) => view('supervisor-manager::components.log-viewer-modal', [
                'process' => $arguments['group'].':'.$arguments['name'],
                'type' => 'stderr',
            ]))
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }

    public function formatUptime(int $start): string
    {
        if ($start == 0) {
            return 'Not running';
        }

        return Carbon::createFromTimestamp($start)->diffForHumans();
    }

    public function render(): View
    {
        return view('supervisor-manager::livewire.process-list', [
            'processes' => $this->processes,
        ]);
    }
}
