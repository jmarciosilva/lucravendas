<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\RegisterForEvent;

/**
 * Command de inscrição em um item de agenda.
 *
 * Imutável (readonly) — transporta os dados necessários para o handler.
 */
final readonly class RegisterForEventCommand
{
    public function __construct(
        /** ID do tenant (loja) */
        public string $tenantId,
        /** ID do item de agenda */
        public int $agendaItemId,
        /** ID do usuário que está se inscrevendo */
        public int $userId,
        /** Observações opcionais do inscrito */
        public ?string $notes = null,
    ) {}
}
