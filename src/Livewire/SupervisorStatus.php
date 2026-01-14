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
