<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use Livewire\Component;

/**
 * Componente de filtros do catálogo de produtos.
 * Emite evento para que a página recarregue com os filtros aplicados.
 */
final class CatalogoFiltros extends Component
{
    public string  $tenantId   = '';
    public string  $tenantSlug = '';
    public ?int    $categoriaId = null;
    public ?string $precoMin   = null;
    public ?string $precoMax   = null;
    public string  $busca      = '';
    public string  $ordenacao  = 'recente';

    public array $categorias = [];

    public function mount(string $tenantId, string $tenantSlug): void
    {
        $this->tenantId   = $tenantId;
        $this->tenantSlug = $tenantSlug;

        // Carrega categorias do tenant para o select
        $this->categorias = CategoryModel::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function aplicarFiltros(): void
    {
        $this->redirect(route('loja.produtos.index', $this->tenantSlug) . '?' . http_build_query(
            array_filter([
                'categoria' => $this->categoriaId,
                'preco_min' => $this->precoMin,
                'preco_max' => $this->precoMax,
                'q'         => $this->busca,
                'ordem'     => $this->ordenacao !== 'recente' ? $this->ordenacao : null,
            ])
        ));
    }

    public function limparFiltros(): void
    {
        $this->categoriaId = null;
        $this->precoMin    = null;
        $this->precoMax    = null;
        $this->busca       = '';
        $this->ordenacao   = 'recente';
        $this->redirect(route('loja.produtos.index', $this->tenantSlug));
    }

    public function render()
    {
        return view('storefront.livewire.catalogo-filtros');
    }
}
