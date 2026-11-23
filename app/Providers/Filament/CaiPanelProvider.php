<?php

namespace App\Providers\Filament;

use Cai\Identity\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panel de administración del CAI (F2 E-06).
 *
 * Las policies de identity deciden qué ve cada rol: el panel no tiene
 * bypass ni menús "ocultos" que después filtren por URL.
 */
final class CaiPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('panel')
            ->path('panel')
            ->login(Login::class)
            ->colors(['primary' => Color::Blue])
            ->discoverResources(
                in: __DIR__.'/../../../packages/identity/src/Filament/Resources',
                for: 'Cai\\Identity\\Filament\\Resources',
            )
            ->discoverResources(
                in: __DIR__.'/../../../packages/students/src/Filament/Resources',
                for: 'Cai\\Students\\Filament\\Resources',
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
            ]);
    }
}
