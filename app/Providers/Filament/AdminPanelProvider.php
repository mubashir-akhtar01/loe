<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AdminDashboardHero;
use App\Filament\Widgets\LoeAdminStatsOverview;
use App\Filament\Widgets\LoeUtilizationChart;
use App\Filament\Widgets\PendingLoeReportsTable;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): View => view('filament.auth.background', [
                    'eyebrow' => 'Admin access',
                    'panelLabel' => 'Admin panel',
                    'tone' => 'admin',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Admin access',
                    'headline' => 'Step into the command center',
                    'highlights' => ['Review submissions fast', 'Spot staffing pressure early'],
                    'panelLabel' => 'Admin panel',
                    'tone' => 'admin',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Admin recovery',
                    'headline' => 'Get back to the command center',
                    'highlights' => ['Restore access securely', 'Return to approvals without friction'],
                    'panelLabel' => 'Admin panel',
                    'tone' => 'admin',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Admin recovery',
                    'headline' => 'Create a fresh admin password',
                    'highlights' => ['Protect the panel', 'Continue staffing work with confidence'],
                    'panelLabel' => 'Admin panel',
                    'tone' => 'admin',
                ]),
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminDashboardHero::class,
                LoeAdminStatsOverview::class,
                LoeUtilizationChart::class,
                PendingLoeReportsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
