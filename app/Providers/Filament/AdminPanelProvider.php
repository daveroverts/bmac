<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
            // Proof of concept: lives next to the existing /admin pages so both can be compared.
            ->path('filament')
            // No ->login(): admins log in through VATSIM Connect on the main site;
            // unauthenticated visitors are sent to the app's own login route.
            ->brandName(fn (): string => config('app.title'))
            ->colors([
                // Bootswatch Flatly colours. generateV3Palette keeps the low saturation of #2C3E50.
                'primary' => Color::generateV3Palette('#2C3E50'),
                'success' => Color::hex('#18BC9C'),
                'danger' => Color::hex('#E74C3C'),
                'warning' => Color::hex('#F39C12'),
            ])
            ->navigationItems([
                NavigationItem::make(__('Back to site'))
                    ->url(fn (): string => route('home'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->sort(100),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
