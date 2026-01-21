<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\View\View;
use Iperamuna\LaravelSupervisorManager\Facades\SupervisorApi;
use Livewire\Component;

class SupervisorStatus extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

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

    public function startAction(): Action
    {
        return Action::make('start')
            ->label('Start All Processes')
            ->icon('heroicon-m-play')
            ->color('success')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Start All Processes')
            ->modalDescription('Are you sure you want to start all processes?')
            ->action(function () {
                try {
                    SupervisorApi::start(true);
                    Notification::make()->title('All processes started.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to start processes.')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function stopAction(): Action
    {
        return Action::make('stop')
            ->label('Stop All Processes')
            ->icon('heroicon-m-stop')
            ->color('danger')
            ->iconButton()
            ->action(function () {
                try {
                    SupervisorApi::stop(true);
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
