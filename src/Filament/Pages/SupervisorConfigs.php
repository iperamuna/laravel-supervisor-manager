<?php

namespace Iperamuna\LaravelSupervisorManager\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SupervisorConfigs extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'supervisor-manager::filament.pages.supervisor-configs';

    public function getTitle(): string|Htmlable
    {
        return 'Configuration';
    }

    public static function getNavigationLabel(): string
    {
        return 'Configurations';
    }
}
