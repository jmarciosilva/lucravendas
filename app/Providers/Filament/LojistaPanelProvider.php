<?php

declare(strict_types=1);

namespace App\Providers\Filament;

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
 * Provider do painel do lojista.
 *
 * Disponível em /painel — exclusivo para tenant_admins.
 * Todos os Resources escopam automaticamente ao tenant_id do usuário autenticado.
 */
class LojistaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('lojista')
            ->path('painel')
            ->login()
            ->brandName('Minha Loja')
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(
                in: app_path('Modules/Lojista/Presentation/Resources'),
                for: 'App\\Modules\\Lojista\\Presentation\\Resources'
            )
            ->discoverPages(
                in: app_path('Modules/Lojista/Presentation/Pages'),
                for: 'App\\Modules\\Lojista\\Presentation\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/Lojista/Presentation/Widgets'),
                for: 'App\\Modules\\Lojista\\Presentation\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Catálogo'),
                NavigationGroup::make('Pedidos'),
                NavigationGroup::make('Clientes'),
                NavigationGroup::make('Frete'),
                NavigationGroup::make('Marketing'),
                NavigationGroup::make('Agenda'),
                NavigationGroup::make('Configurações'),
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
     * Restringe o acesso ao painel apenas para tenant_admins.
     * super_admins e clientes recebem 403 se tentarem acessar /painel.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('tenant_admin');
    }
}
