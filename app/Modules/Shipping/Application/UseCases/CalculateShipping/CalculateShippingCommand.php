<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Application\UseCases\CalculateShipping;

final class CalculateShippingCommand
{
    public function __construct(
        public readonly string  $tenantId,
        public readonly string  $toZipcode,
        public readonly string  $toState,       // UF — necessário para tabela interna
        public readonly ?string $sessionId,     // para identificar o carrinho
        public readonly ?int    $userId,        // para identificar o carrinho autenticado
        public readonly int     $subtotalCentavos, // para verificar threshold de frete grátis
    ) {}
}
