<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\AddCartItem;

final readonly class AddCartItemCommand
{
    public function __construct(
        public string  $tenantId,
        public ?string $sessionId,
        public ?int    $userId,
        public int     $productId,
        public ?int    $variantId,
        public int     $quantity,
    ) {}
}
