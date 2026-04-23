<?php

namespace App\Providers\Filament;

use App\Filament\Employee\Pages\Dashboard;
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

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employee')
            ->path('employee')
            ->login()
            ->passwordReset()
            ->databaseNotifications()
            ->profile()
            ->brandName('LOE HUB')
            ->viteTheme('resources/css/filament/employee/theme.css')
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): View => view('filament.auth.background', [
                    'eyebrow' => 'Employee workspace',
                    'panelLabel' => 'Employee panel',
                    'tone' => 'employee',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Employee workspace',
                    'headline' => 'Walk back into your reporting rhythm',
                    'highlights' => ['Update monthly effort cleanly', 'Track capacity before it becomes pressure'],
                    'panelLabel' => 'Employee panel',
                    'tone' => 'employee',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Employee recovery',
                    'headline' => 'Reconnect to your monthly workspace',
                    'highlights' => ['Recover access quickly', 'Keep reporting momentum intact'],
                    'panelLabel' => 'Employee panel',
                    'tone' => 'employee',
                ]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_BEFORE,
                fn (): View => view('filament.auth.panel-intro', [
                    'eyebrow' => 'Employee recovery',
                    'headline' => 'Set a fresh password and keep moving',
                    'highlights' => ['Protect your account', 'Return to reporting with confidence'],
                    'panelLabel' => 'Employee panel',
                    'tone' => 'employee',
                ]),
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverPages(in: app_path('Filament/Employee/Pages'), for: 'App\Filament\Employee\Pages')
            ->pages([
                Dashboard::class,
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
                'verified',
            ]);
    }
}
