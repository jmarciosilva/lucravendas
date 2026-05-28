<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\UpdateProduct;

use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orquestra a atualização dos dados de um produto existente.
 *
 * Valida que o produto pertence ao tenant da requisição antes de qualquer
 * modificação, evitando que um tenant edite produtos de outro.
 */
final class UpdateProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly Dispatcher $events,
    ) {}

    public function handle(UpdateProductCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $product = $this->repository->findById($command->productId, $command->tenantId);

            if ($product === null) {
                throw new RuntimeException('Produto não encontrado.');
            }

            $product->update(
                name: $command->name,
                description: $command->description,
                price: Money::fromCentavos($command->price),
                comparePrice: $command->comparePrice !== null
                    ? Money::fromCentavos($command->comparePrice)
                    : null,
                sku: $command->sku,
                categoryId: $command->categoryId,
            );

            $this->repository->save($product);

            // Despacha eventos — o listener do Scout reindexará o produto no Meilisearch
            foreach ($product->pullDomainEvents() as $event) {
                $this->events->dispatch($event);
            }
        });
    }
}
