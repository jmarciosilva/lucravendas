<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Repositories;

use App\Modules\Orders\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): Order;

    public function update(Order $order): void;

    public function findById(int $id, string $tenantId): ?Order;

    /** Busca sem restrição de tenant — uso interno (webhook, admin). */
    public function findByIdRaw(int $id): ?Order;

    /** @return Order[] */
    public function findByUser(int $userId, string $tenantId): array;
}
