<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Modules\Admin\Presentation\Widgets\PlatformStatsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
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

/**
 * Provider do painel administrativo central da LucraOne.
 *
 * Disponível em /admin — exclusivo para super_admins da plataforma.
 * Lojistas e clientes NÃO têm acesso a este painel.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('LucraVendas Admin')
            ->colors([
                'primary' => Color::Violet,
            ])
            // Resources ficam no módulo Admin para manter o DDD
            ->discoverResources(
                in: app_path('Modules/Admin/Presentation/Resources'),
                for: 'App\\Modules\\Admin\\Presentation\\Resources'
            )
            ->discoverPages(
                in: app_path('Modules/Admin/Presentation/Pages'),
                for: 'App\\Modules\\Admin\\Presentation\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/Admin/Presentation/Widgets'),
                for: 'App\\Modules\\Admin\\Presentation\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Plataforma')->icon('heroicon-o-building-storefront'),
                NavigationGroup::make('Usuários')->icon('heroicon-o-users'),
            ])
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
            ->authGuard('web');
    }

    /**
     * Restringe o acesso ao painel apenas para usuários com role super_admin.
     * Lojistas e clientes recebem 403 se tentarem acessar /admin.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super_admin');
    }
}
