<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Storefront\Presentation\Livewire\AdicionarAoCarrinho;
use App\Modules\Storefront\Presentation\Livewire\AgendaCard;
use App\Modules\Storefront\Presentation\Livewire\CarrinhoPage;
use App\Modules\Storefront\Presentation\Livewire\CarrinhoWidget;
use App\Modules\Storefront\Presentation\Livewire\CatalogoFiltros;
use App\Modules\Storefront\Presentation\Livewire\CheckoutForm;
use App\Modules\Storefront\Presentation\Livewire\InscricaoAgenda;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class StorefrontServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('storefront.carrinho-widget',    CarrinhoWidget::class);
        Livewire::component('storefront.carrinho-page',      CarrinhoPage::class);
        Livewire::component('storefront.adicionar-ao-carrinho', AdicionarAoCarrinho::class);
        Livewire::component('storefront.catalogo-filtros',   CatalogoFiltros::class);
        Livewire::component('storefront.checkout-form',      CheckoutForm::class);
        Livewire::component('storefront.inscricao-agenda',   InscricaoAgenda::class);
        Livewire::component('storefront.agenda-card',        AgendaCard::class);
    }
}
