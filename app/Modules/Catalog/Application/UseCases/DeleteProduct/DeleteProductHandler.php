<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\DeleteProduct;

use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orquestra a remoção lógica (soft delete) de um produto.
 *
 * O produto não é apagado fisicamente do banco para preservar
 * o histórico de pedidos que fazem referência a ele.
 */
final class DeleteProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function handle(int $productId, string $tenantId): void
    {
        DB::transaction(function () use ($productId, $tenantId) {
            // Confirma que o produto existe e pertence ao tenant antes de deletar
            $product = $this->repository->findById($productId, $tenantId);

            if ($product === null) {
                throw new RuntimeException('Produto não encontrado.');
            }

            $this->repository->delete($productId, $tenantId);
        });
    }
}
