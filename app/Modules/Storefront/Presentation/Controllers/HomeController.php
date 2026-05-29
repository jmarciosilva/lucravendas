<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Support\StorefrontContext;
use Illuminate\View\View;

final class HomeController
{
    public function __construct(
        private readonly ListCategoriesHandler $categoriesHandler,
    ) {}

    public function __invoke(): View
    {
        $tenantId = StorefrontContext::tenantId();

        $categorias = $this->categoriesHandler->handle($tenantId);

        $destaques = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->latest()
            ->limit(8)
            ->get();

        return view('storefront.home', compact('categorias', 'destaques'));
    }
}
