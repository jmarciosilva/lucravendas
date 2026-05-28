<?php

declare(strict_types=1);

namespace App\Modules\Orders\Domain\Repositories;

use App\Modules\Orders\Domain\Entities\Cart;

interface CartRepositoryInterface
{
    public function findBySessionId(string $sessionId, string $tenantId): ?Cart;

    public function findByUserId(int $userId, string $tenantId): ?Cart;

    public function save(Cart $cart): Cart;

    public function delete(int $cartId): void;
}
