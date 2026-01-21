<?php

namespace Iperamuna\LaravelSupervisorManager\Filament\Pages;

use Filament\Pages\Page;

class RedisContentPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Redis Search Content';

    protected static ?string $title = 'Redis Search Content';

    protected static ?string $slug = 'redis-search-content';

    protected string $view = 'supervisor-manager::filament.pages.redis-content-page';

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }
}
