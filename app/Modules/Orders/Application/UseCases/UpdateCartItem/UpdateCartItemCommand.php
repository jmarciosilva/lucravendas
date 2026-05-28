<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\UpdateCartItem;

final readonly class UpdateCartItemCommand
{
    public function __construct(
        public string  $tenantId,
        public ?string $sessionId,
        public ?int    $userId,
        public int     $itemId,
        public int     $quantity,
    ) {}
}
