<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Domain\ValueObjects;

use RuntimeException;

final class ShippingAddress
{
    public function __construct(
        public readonly string  $recipientName,
        public readonly string  $zipcode,
        public readonly string  $address,
        public readonly string  $number,
        public readonly ?string $complement,
        public readonly string  $city,
        public readonly string  $state,   // UF — 2 chars
    ) {
        if (strlen($state) !== 2) {
            throw new RuntimeException('Estado deve ser a sigla UF com 2 caracteres.');
        }
    }

    /** Retorna o CEP sem hífen */
    public function zipcodeDigitsOnly(): string
    {
        return preg_replace('/\D/', '', $this->zipcode);
    }
}
