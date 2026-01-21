<?php

namespace Iperamuna\LaravelSupervisorManager\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Iperamuna\LaravelSupervisorManager\Services\SupervisorApiService;
use Iperamuna\LaravelSupervisorManager\Services\SupervisorConfigService;
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

        // Fetch running processes to determine status
        try {
            /** @var SupervisorApiService $api */
            $api = app(SupervisorApiService::class);
            $processes = $api->getAllProcessInfo();
        } catch (\Exception $e) {
            $processes = [];
        }

        // Map status
        foreach ($allConfigs as &$config) {
            $program = $config['program'] ?? '';
            $config['is_running'] = false;

            foreach ($processes as $process) {
                // Check if process belongs to this config group and is RUNNING (state=20)
                if (($process['group'] ?? '') === $program && ($process['state'] ?? 0) === 20) {
                    $config['is_running'] = true;
                    break;
                }
            }
        }
        unset($config);

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

                try {
                    $service->syncToSystem($filename);

                    // Check if using secure copy mode
                    $useSecureCopy = config('supervisor-manager.use_secure_copy', true);

                    if ($useSecureCopy) {
                        // Secure copy script already ran supervisorctl reread & update
                        Notification::make()
                            ->title('Deployed Successfully')
                            ->body('Configuration synced and supervisor reloaded via secure copy script.')
                            ->success()
                            ->send();
                    } else {
                        // Legacy mode - need to manually run supervisorctl commands
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
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Deployment Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function stopAction(): Action
    {
        return Action::make('stop')
            ->label('Stop')
            ->icon('heroicon-m-stop')
            ->color('danger')
            ->iconButton()
            ->action(function (array $arguments) {
                $program = $arguments['program'];
                try {
                    /** @var SupervisorApiService $api */
                    $api = app(SupervisorApiService::class);
                    // Stop the group (program name is the group name)
                    $api->stopProcess($program.':*', true);

                    Notification::make()->title('Processes stopped successfully')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to stop processes')->body($e->getMessage())->danger()->send();
                }
            });
    }

    public function startAction(): Action
    {
        return Action::make('start')
            ->label('Start')
            ->icon('heroicon-m-play')
            ->color('success')
            ->iconButton()
            ->requiresConfirmation()
            ->modalHeading('Start Processes')
            ->modalDescription('Are you sure you want to start all processes in this group?')
            ->action(function (array $arguments) {
                $program = $arguments['program'];
                try {
                    /** @var SupervisorApiService $api */
                    $api = app(SupervisorApiService::class);
                    $api->startProcess($program.':*', true);

                    Notification::make()->title('Processes started successfully')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to start processes')->body($e->getMessage())->danger()->send();
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
