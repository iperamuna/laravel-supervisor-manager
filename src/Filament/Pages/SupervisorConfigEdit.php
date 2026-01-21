<?php

namespace Iperamuna\LaravelSupervisorManager\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Iperamuna\LaravelSupervisorManager\Services\SupervisorConfigService;

class SupervisorConfigEdit extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'supervisor-manager::filament.pages.supervisor-config-edit';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public ?string $filename = null;

    public function getTitle(): string|Htmlable
    {
        return $this->filename ? 'Edit Configuration' : 'New Configuration';
    }

    public function mount(): void
    {
        $this->filename = request()->query('file');

        if ($this->filename) {
            $service = app(SupervisorConfigService::class);
            $config = $service->getConfig($this->filename);

            if ($config) {
                // Ensure boolean strings are converted for checkboxes
                foreach (['autostart', 'autorestart', 'stopasgroup', 'killasgroup', 'redirect_stderr'] as $key) {
                    if (isset($config[$key]) && $config[$key] === 'true') {
                        $config[$key] = true;
                    } elseif (isset($config[$key]) && $config[$key] === 'false') {
                        $config[$key] = false;
                    }
                }
                $this->form->fill($config);
            }
        } else {
            $this->form->fill([
                'autostart' => true,
                'autorestart' => true,
                'startsecs' => 1,
                'stopwaitsecs' => 10,
                'numprocs' => 1,
                'process_name' => '%(program_name)s_%(process_num)02d',
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Program Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('program')
                                ->label('Program Name')
                                ->required()
                                ->helperText('The name of the program (e.g. laravel-queue). Used for the [program:x] section.'),

                            TextInput::make('process_name')
                                ->label('Process Name Details')
                                ->default('%(program_name)s_%(process_num)02d')
                                ->required()
                                ->helperText('Expression for process name. Default: %(program_name)s_%(process_num)02d'),
                        ]),

                        TextInput::make('command')
                            ->label('Command')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('The full command to run (e.g. /usr/bin/php /path/to/artisan queue:work)'),

                        Grid::make(3)->schema([
                            TextInput::make('user')
                                ->label('User')
                                ->placeholder('root')
                                ->helperText('System user to run the process as.'),

                            TextInput::make('numprocs')
                                ->label('Number of Processes')
                                ->numeric()
                                ->default(1)
                                ->minValue(1),

                            TextInput::make('directory')
                                ->label('Directory')
                                ->helperText('Working directory for the process'),
                        ]),
                    ]),

                Section::make('Logs')
                    ->schema([
                        Checkbox::make('redirect_stderr')
                            ->label('Redirect stderr to stdout')
                            ->default(true),

                        Grid::make(2)->schema([
                            TextInput::make('stdout_logfile')
                                ->label('Stdout Log File')
                                ->placeholder('/var/log/supervisor/example.log'),

                            TextInput::make('stderr_logfile')
                                ->label('Stderr Log File')
                                ->placeholder('/var/log/supervisor/example.err.log')
                                ->visible(fn ($get) => ! $get('redirect_stderr')),
                        ]),
                    ]),

                Section::make('Process Control')
                    ->description('Behavior for starting and stopping')
                    ->schema([
                        Grid::make(4)->schema([
                            Checkbox::make('autostart')
                                ->label('Autostart')
                                ->default(true),

                            Checkbox::make('autorestart')
                                ->label('Autorestart')
                                ->default(true),

                            Checkbox::make('stopasgroup')
                                ->label('Stop as Group')
                                ->default(true),

                            Checkbox::make('killasgroup')
                                ->label('Kill as Group')
                                ->default(true),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('startsecs')
                                ->label('Start Seconds')
                                ->numeric()
                                ->default(1),

                            TextInput::make('stopwaitsecs')
                                ->label('Stop Wait Seconds')
                                ->numeric()
                                ->default(10),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $service = app(SupervisorConfigService::class);

        // determine filename
        $filename = $this->filename ?? ($data['program'].'.conf');

        if ($service->saveConfig($filename, $data)) {
            Notification::make()
                ->title('Configuration Saved')
                ->success()
                ->send();

            // Redirect to list
            $this->redirect(SupervisorConfigs::getUrl());
        } else {
            Notification::make()
                ->title('Error Saving Configuration')
                ->body('Check permissions for '.config('supervisor-manager.conf_path'))
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Configuration')
                ->submit('save'),
            Action::make('cancel')
                ->label('Cancel')
                ->url(SupervisorConfigs::getUrl())
                ->color('gray'),
        ];
    }
}
