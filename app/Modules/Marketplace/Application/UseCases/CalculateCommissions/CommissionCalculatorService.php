<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Application\UseCases\CalculateCommissions;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Marketplace\Domain\Entities\Commission;
use App\Modules\Marketplace\Domain\Repositories\CommissionRepositoryInterface;
use App\Modules\Marketplace\Domain\Repositories\SellerRepositoryInterface;
use App\Modules\Orders\Infrastructure\Models\OrderItemModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;

/**
 * Calcula e persiste comissões para os itens de um pedido recém-criado.
 *
 * Deve ser chamado dentro da transação do checkout, após os itens serem salvos.
 * Itens de produtos sem seller associado são ignorados (produto do próprio tenant).
 */
final class CommissionCalculatorService
{
    public function __construct(
        private readonly SellerRepositoryInterface    $sellerRepository,
        private readonly CommissionRepositoryInterface $commissionRepository,
    ) {}

    /**
     * @param array<int, ProductModel> $productSnapshots Mapa product_id → ProductModel
     */
    public function calculateForOrder(int $orderId, array $productSnapshots): void
    {
        $items = OrderItemModel::where('order_id', $orderId)->get();

        $commissions = [];

        foreach ($items as $item) {
            $product = $productSnapshots[$item->product_id] ?? null;

            if ($product === null || ! $product->seller_id) {
                // Produto sem seller — comissão não se aplica
                continue;
            }

            $seller = $this->sellerRepository->findById($product->seller_id);

            if ($seller === null || ! $seller->status()->isActive()) {
                continue;
            }

            $grossAmount = Money::fromCentavos($item->unit_price * $item->quantity);

            $commissions[] = Commission::calculate(
                orderItemId: $item->id,
                sellerId: $seller->id(),
                grossAmount: $grossAmount,
                commissionRate: $seller->commissionRate(),
            );
        }

        if (! empty($commissions)) {
            $this->commissionRepository->saveMany($commissions);
        }
    }
}
