<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Repositories;

use App\Modules\Orders\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): Order;

    public function findById(int $id, string $tenantId): ?Order;

    /** @return Order[] */
    public function findByUser(int $userId, string $tenantId): array;
}
