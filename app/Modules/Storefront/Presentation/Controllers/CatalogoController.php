<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Support\StorefrontContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CatalogoController
{
    public function __construct(
        private readonly ListCategoriesHandler $categoriesHandler,
    ) {}

    public function index(Request $request): View
    {
        $tenantId   = StorefrontContext::tenantId();
        $categorias = $this->categoriesHandler->handle($tenantId);

        // Filtros opcionais via query string
        $categoriaId = $request->query('categoria');
        $precoMin    = $request->query('preco_min');
        $precoMax    = $request->query('preco_max');
        $busca       = $request->query('q');
        $ordenacao   = $request->query('ordem', 'recente');

        $query = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->when($categoriaId, fn ($q) => $q->where('category_id', $categoriaId))
            ->when($precoMin, fn ($q) => $q->where('price', '>=', (int) round((float) $precoMin * 100)))
            ->when($precoMax, fn ($q) => $q->where('price', '<=', (int) round((float) $precoMax * 100)))
            ->when($busca, fn ($q) => $q->where('name', 'like', "%{$busca}%"));

        $query = match ($ordenacao) {
            'preco_asc'  => $query->orderBy('price'),
            'preco_desc' => $query->orderByDesc('price'),
            'nome'       => $query->orderBy('name'),
            default      => $query->latest(),
        };

        $produtos = $query->paginate(12)->withQueryString();

        $categoriaAtual = $categoriaId
            ? CategoryModel::where('id', $categoriaId)->where('tenant_id', $tenantId)->first()
            : null;

        return view('storefront.catalogo.index', compact(
            'produtos', 'categorias', 'categoriaAtual',
            'busca', 'ordenacao', 'precoMin', 'precoMax',
        ));
    }

    public function show(Request $request, string $tenantSlug, string $slug): View
    {
        $tenantId = StorefrontContext::tenantId();

        $produto = ProductModel::where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('variants')
            ->firstOrFail();

        return view('storefront.produto.show', compact('produto'));
    }
}
