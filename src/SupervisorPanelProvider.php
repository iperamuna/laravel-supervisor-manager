<?php

namespace Iperamuna\LaravelSupervisorManager;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path(config('supervisor-manager.panel_url', 'supervisor'))
            ->login()
            ->brandName('Supervisor Manager')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'Iperamuna\\LaravelSupervisorManager\\Filament\\Resources')
            ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'Iperamuna\\LaravelSupervisorManager\\Filament\\Pages')
            ->pages([
                Filament\Pages\SupervisorDashboard::class,
            ])
            ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'Iperamuna\\LaravelSupervisorManager\\Filament\\Widgets')
            ->widgets([
                    // Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn(): string => \Illuminate\Support\Facades\Blade::render('@livewire(\'supervisor-manager::supervisor-status\')')
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->theme(asset('vendor/supervisor-manager/theme.css'));
    }
}
