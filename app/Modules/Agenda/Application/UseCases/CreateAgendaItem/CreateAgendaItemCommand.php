<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\CreateAgendaItem;

/**
 * Command para criação de um item de agenda.
 *
 * Imutável (readonly) — transporta todos os dados necessários para criação.
 */
final readonly class CreateAgendaItemCommand
{
    public function __construct(
        /** ID do tenant (loja) */
        public string $tenantId,
        /** ID do seller opcional (null = item do tenant) */
        public ?int $sellerId,
        /** Tipo: evento, curso, workshop */
        public string $type,
        /** Título do item */
        public string $title,
        /** Descrição completa opcional */
        public ?string $description,
        /** Descrição curta opcional (máx 500 caracteres) */
        public ?string $shortDescription,
        /** URL da imagem de destaque opcional */
        public ?string $featuredImageUrl,
        /** Data e hora de início (string datetime) */
        public string $startsAt,
        /** Data e hora de fim (string datetime) */
        public string $endsAt,
        /** Local do evento opcional */
        public ?string $location,
        /** Número máximo de vagas (null = ilimitado) */
        public ?int $slots,
        /** Preço em centavos (0 = gratuito) */
        public int $priceCentavos = 0,
    ) {}
}
