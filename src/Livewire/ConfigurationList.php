<?php

namespace Iperamuna\SupervisorManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Iperamuna\SupervisorManager\Services\SupervisorConfigService;
use Livewire\Component;

class ConfigurationList extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $search = '';

    public function getConfigsProperty(): array
    {
        $service = app(SupervisorConfigService::class);
        $allConfigs = $service->listConfigs();

        if (empty($this->search)) {
            return $allConfigs;
        }

        $term = strtolower($this->search);

        return array_filter($allConfigs, function ($config) use ($term) {
            return str_contains(strtolower($config['name'] ?? ''), $term) ||
                str_contains(strtolower($config['program'] ?? ''), $term) ||
                str_contains(strtolower($config['user'] ?? ''), $term) ||
                str_contains(strtolower($config['command'] ?? ''), $term);
        });
    }

    public function syncAction(): Action
    {
        return Action::make('sync')
            ->label('Sync')
            ->icon('heroicon-m-arrow-path')
            ->color('gray')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Sync Configuration')
            ->modalDescription('Are you sure you want to copy this configuration to the system supervisor directory? This will overwrite any existing file.')
            ->modalSubmitActionLabel('Yes, Sync')
            ->action(function (array $arguments) {
                $filename = $arguments['filename'];
                $service = app(SupervisorConfigService::class);
                if ($service->syncToSystem($filename)) {
                    Notification::make()->title('Configuration synced to system path.')->success()->send();
                } else {
                    Notification::make()->title('Failed to sync. Check permissions.')->danger()->send();
                }
            });
    }

    public function deployAction(): Action
    {
        return Action::make('deploy')
            ->label('Deploy')
            ->icon('heroicon-m-play')
            ->color('primary')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Deploy Configuration')
            ->modalDescription('This will copy the configuration to the system, update supervisor, and reload processes. Continue?')
            ->modalSubmitActionLabel('Deploy & Reload')
            ->action(function (array $arguments) {
                $filename = $arguments['filename'];
                // First sync
                $service = app(SupervisorConfigService::class);
                if (! $service->syncToSystem($filename)) {
                    Notification::make()->title('Failed to sync. Deployment aborted.')->danger()->send();

                    return;
                }

                // Then deploy/reload
                $result = $service->deployChanges();

                if ($result['exit_code'] === 0) {
                    Notification::make()
                        ->title('Deployed Successfully')
                        ->body($result['output'])
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Deploy Finished with Errors')
                        ->body($result['output'])
                        ->warning()
                        ->send();
                }
            });
    }

    public function render(): View
    {
        return view('supervisor-manager::livewire.configuration-list', [
            'configs' => $this->configs,
        ]);
    }
}
