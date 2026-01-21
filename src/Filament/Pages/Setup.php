<?php

namespace Iperamuna\LaravelSupervisorManager\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Setup extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected string $view = 'supervisor-manager::filament.pages.setup';

    protected static ?int $navigationSort = 10;

    public function getTitle(): string|Htmlable
    {
        return 'Setup Guide';
    }

    public static function getNavigationLabel(): string
    {
        return 'Setup';
    }
}
