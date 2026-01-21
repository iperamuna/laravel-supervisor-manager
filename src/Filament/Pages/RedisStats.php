<?php

namespace Iperamuna\LaravelSupervisorManager\Filament\Pages;

use Filament\Pages\Page;

class RedisStats extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationLabel = 'Redis Status';

    protected static ?string $title = 'Redis Status';

    protected static ?string $slug = 'redis-status';

    protected string $view = 'supervisor-manager::filament.pages.redis-stats';

    // Ensure it appears in navigation
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }
}
