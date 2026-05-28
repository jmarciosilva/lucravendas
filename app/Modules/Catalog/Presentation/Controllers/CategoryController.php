<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\UseCases\CreateCategory\CreateCategoryCommand;
use App\Modules\Catalog\Application\UseCases\CreateCategory\CreateCategoryHandler;
use App\Modules\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Modules\Catalog\Presentation\Requests\CreateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Ponto de entrada HTTP para o recurso de categorias.
 *
 * Expõe a árvore de categorias publicamente (leitura) e
 * restringe a criação a usuários com role tenant_admin (escrita).
 */
final class CategoryController extends Controller
{
    public function __construct(
        private readonly ListCategoriesHandler $listHandler,
        private readonly CreateCategoryHandler $createHandler,
    ) {}

    /**
     * Lista todas as categorias ativas do tenant em formato de árvore hierárquica.
     * Endpoint público — não requer autenticação.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // O tenant_id vem do middleware de tenancy (subdomínio ou header X-Tenant-ID)
            $tenantId = $request->header('X-Tenant-ID', '');

            $tree = $this->listHandler->handle($tenantId);

            return response()->json(['data' => $tree]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar categorias.'], 500);
        }
    }

    /**
     * Cria uma nova categoria no catálogo do tenant.
     * Requer autenticação e role tenant_admin.
     */
    public function store(CreateCategoryRequest $request): JsonResponse
    {
        try {
            $output = $this->createHandler->handle(new CreateCategoryCommand(
                name: $request->validated('name'),
                slug: $request->validated('slug'),
                tenantId: $request->header('X-Tenant-ID', ''),
                parentId: $request->validated('parent_id'),
                sortOrder: (int) $request->validated('sort_order', 0),
            ));

            return response()->json(['data' => $output], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
