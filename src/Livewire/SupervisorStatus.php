<?php

namespace Iperamuna\SupervisorManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Iperamuna\SupervisorManager\Facades\SupervisorApi;
use Livewire\Component;

class SupervisorStatus extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $status = 'CHECKING';

    public bool $isRunning = false;

    public bool $isError = false;

    public function mount(): void
    {
        $this->checkStatus();
    }

    public function checkStatus(): void
    {
        try {
            $state = SupervisorApi::getState();
            $this->status = $state['statename'] ?? 'UNKNOWN';
            $code = $state['statecode'] ?? 0;

            // 1 = RUNNING
            $this->isRunning = $code === 1;
            $this->isError = false;
        } catch (\Exception $e) {
            $this->status = 'OFFLINE';
            $this->isRunning = false;
            $this->isError = true;
        }
    }

    public function startAllAction(): Action
    {
        return Action::make('start_all')
            ->label('Start All Processes')
            ->icon('heroicon-m-play')
            ->color('success')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Start All Processes')
            ->modalDescription('Are you sure you want to start all processes?')
            ->action(function () {
                try {
                    SupervisorApi::startAllProcesses(true);
                    Notification::make()->title('All processes started.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to start processes.')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function stopAllAction(): Action
    {
        return Action::make('stop_all')
            ->label('Stop All Processes')
            ->icon('heroicon-m-stop')
            ->color('danger')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Stop All Processes')
            ->modalDescription('Are you sure you want to stop all processes?')
            ->action(function () {
                try {
                    SupervisorApi::stopAllProcesses(true);
                    Notification::make()->title('All processes stopped.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to stop processes.')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function restartAction(): Action
    {
        return Action::make('restart')
            ->label('Restart')
            ->icon('heroicon-m-arrow-path')
            ->color('warning')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Restart Supervisor')
            ->modalDescription('Are you involved in a configuration process or just want to fresh start supervisor? This will restart the main Supervisor process.')
            ->modalSubmitActionLabel('Yes, Restart')
            ->action(function () {
                try {
                    if (SupervisorApi::restart()) {
                        Notification::make()->title('Supervisor restart signal sent.')->success()->send();
                        // The status polling will eventually pick up the new state, though it might error briefly while down
                        $this->checkStatus();
                    } else {
                        throw new \Exception('Failed to send restart signal');
                    }
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to restart Supervisor.')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function render(): View
    {
        return view('supervisor-manager::livewire.supervisor-status');
    }
}
