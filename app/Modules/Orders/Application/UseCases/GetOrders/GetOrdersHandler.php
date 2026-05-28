<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\GetOrders;

use App\Modules\Orders\Domain\Entities\Order;
use App\Modules\Orders\Domain\Repositories\OrderRepositoryInterface;

final class GetOrdersHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {}

    /** @return Order[] */
    public function handle(int $userId, string $tenantId): array
    {
        return $this->repository->findByUser($userId, $tenantId);
    }
}
