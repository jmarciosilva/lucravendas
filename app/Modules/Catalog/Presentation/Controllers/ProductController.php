<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\UseCases\CreateProduct\CreateProductCommand;
use App\Modules\Catalog\Application\UseCases\CreateProduct\CreateProductHandler;
use App\Modules\Catalog\Application\UseCases\DeleteProduct\DeleteProductHandler;
use App\Modules\Catalog\Application\UseCases\UpdateProduct\UpdateProductCommand;
use App\Modules\Catalog\Application\UseCases\UpdateProduct\UpdateProductHandler;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Catalog\Presentation\Requests\CreateProductRequest;
use App\Modules\Catalog\Presentation\Requests\UpdateProductRequest;
use App\Modules\Catalog\Presentation\Resources\ProductResource;
use App\Support\CacheKeys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Ponto de entrada HTTP para o recurso de produtos.
 *
 * Leitura (index, show, search) é pública.
 * Escrita (store, update, destroy) exige tenant_admin.
 */
final class ProductController extends Controller
{
    public function __construct(
        private readonly CreateProductHandler $createHandler,
        private readonly UpdateProductHandler $updateHandler,
        private readonly DeleteProductHandler $deleteHandler,
    ) {}

    /**
     * Lista produtos ativos do tenant com filtros e paginação.
     * Filtros aceitos: category_id, min_price, max_price (em reais).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');

            $filters = array_filter([
                'category_id' => $request->query('category_id'),
                // Converte filtros de preço de reais para centavos
                'min_price'   => $request->query('min_price')
                    ? (int) round((float) $request->query('min_price') * 100)
                    : null,
                'max_price'   => $request->query('max_price')
                    ? (int) round((float) $request->query('max_price') * 100)
                    : null,
            ]);

            $perPage = min((int) $request->query('per_page', 15), 100);
            $page    = max((int) $request->query('page', 1), 1);

            $cacheKey = CacheKeys::products($tenantId, CacheKeys::hashQuery(
                array_merge($filters, ['per_page' => $perPage, 'page' => $page])
            ));

            $payload = Cache::remember($cacheKey, CacheKeys::PRODUCTS_TTL, function () use (
                $tenantId, $filters, $perPage, $page
            ) {
                $result = ProductModel::query()
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'active')
                    ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
                    ->when(isset($filters['min_price']), fn ($q) => $q->where('price', '>=', $filters['min_price']))
                    ->when(isset($filters['max_price']), fn ($q) => $q->where('price', '<=', $filters['max_price']))
                    ->orderBy('name')
                    ->paginate(perPage: $perPage, page: $page);

                return [
                    'data'  => ProductResource::collection($result->items())->toArray(request()),
                    'meta'  => [
                        'total'        => $result->total(),
                        'per_page'     => $result->perPage(),
                        'current_page' => $result->currentPage(),
                        'last_page'    => $result->lastPage(),
                    ],
                ];
            });

            return response()->json($payload);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar produtos.'], 500);
        }
    }

    /**
     * Retorna os detalhes de um produto pelo slug, com variantes e imagens.
     * Busca apenas produtos ativos — rascunhos são invisíveis no catálogo público.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');

            $product = ProductModel::query()
                ->where('slug', $slug)
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->with('variants')
                ->first();

            if ($product === null) {
                return response()->json(['message' => 'Produto não encontrado.'], 404);
            }

            return response()->json(['data' => new ProductResource($product)]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao buscar produto.'], 500);
        }
    }

    /**
     * Realiza busca full-text via Laravel Scout + Meilisearch.
     * Filtra automaticamente pelo tenant para garantir isolamento.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $query    = (string) $request->query('q', '');

            if (strlen($query) < 2) {
                return response()->json(['message' => 'O termo de busca deve ter ao menos 2 caracteres.'], 422);
            }

            // O Scout busca no Meilisearch e filtra por tenant_id automaticamente
            $results = ProductModel::search($query)
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->paginate(15);

            return response()->json([
                'data' => ProductResource::collection($results->items()),
                'meta' => [
                    'total'        => $results->total(),
                    'current_page' => $results->currentPage(),
                    'last_page'    => $results->lastPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao realizar busca.'], 500);
        }
    }

    /**
     * Cria um novo produto no catálogo do tenant.
     * O produto é criado como rascunho (draft) por padrão.
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $output = $this->createHandler->handle(new CreateProductCommand(
                name: $request->validated('name'),
                slug: $request->validated('slug'),
                price: $request->validated('price'),
                tenantId: $request->header('X-Tenant-ID', ''),
                description: $request->validated('description'),
                comparePrice: $request->validated('compare_price'),
                sku: $request->validated('sku'),
                stock: (int) $request->validated('stock', 0),
                categoryId: $request->validated('category_id'),
            ));

            return response()->json(['data' => $output], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao criar produto.'], 500);
        }
    }

    /**
     * Atualiza os dados de um produto existente.
     * O slug não pode ser alterado após criação.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $this->updateHandler->handle(new UpdateProductCommand(
                productId: $id,
                tenantId: $request->header('X-Tenant-ID', ''),
                name: $request->validated('name'),
                price: $request->validated('price'),
                description: $request->validated('description'),
                comparePrice: $request->validated('compare_price'),
                sku: $request->validated('sku'),
                categoryId: $request->validated('category_id'),
            ));

            return response()->json(['message' => 'Produto atualizado com sucesso.']);
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'não encontrado') ? 404 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao atualizar produto.'], 500);
        }
    }

    /**
     * Remove o produto com soft delete.
     * O registro permanece no banco para preservar histórico de pedidos.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->deleteHandler->handle($id, $request->header('X-Tenant-ID', ''));

            return response()->json(['message' => 'Produto removido com sucesso.']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao remover produto.'], 500);
        }
    }
}
