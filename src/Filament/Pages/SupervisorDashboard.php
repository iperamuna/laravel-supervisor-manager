<?php

namespace Iperamuna\SupervisorManager\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class SupervisorDashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'supervisor-manager::filament.pages.supervisor-dashboard';

    public function getTitle(): string|Htmlable
    {
        return 'Supervisor Dashboard';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
