<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\RegisterForEvent;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use App\Modules\Agenda\Infrastructure\Models\AgendaRegistrationModel;
use Illuminate\Support\Facades\DB;

/**
 * Handler para inscrição em um item de agenda.
 *
 * Processa a inscrição dentro de uma transação com lock para evitar
 * race conditions no controle de vagas.
 */
final class RegisterForEventHandler
{
    /**
     * Realiza a inscrição do usuário no item de agenda.
     *
     * @param  RegisterForEventCommand $command
     * @return AgendaRegistrationModel
     *
     * @throws \RuntimeException Quando o item não está disponível, usuário já inscrito ou vagas esgotadas
     */
    public function handle(RegisterForEventCommand $command): AgendaRegistrationModel
    {
        return DB::transaction(function () use ($command): AgendaRegistrationModel {
            // Busca o item com lock para garantir consistência no controle de vagas
            $item = AgendaItemModel::where('id', $command->agendaItemId)
                ->where('tenant_id', $command->tenantId)
                ->where('status', 'published')
                ->lockForUpdate()
                ->first();

            // Valida existência e disponibilidade do item
            if ($item === null) {
                throw new \RuntimeException('Evento não encontrado ou não disponível.');
            }

            // Verifica se o usuário já está inscrito
            $jaInscrito = AgendaRegistrationModel::where('agenda_item_id', $command->agendaItemId)
                ->where('user_id', $command->userId)
                ->exists();

            if ($jaInscrito) {
                throw new \RuntimeException('Você já está inscrito neste evento.');
            }

            // Verifica disponibilidade de vagas
            if ($item->hasSlots() && $item->slots_used >= $item->slots) {
                throw new \RuntimeException('Vagas esgotadas.');
            }

            // Eventos gratuitos confirmam imediatamente; pagos ficam pendentes
            $status      = $item->isFree() ? 'confirmed' : 'pending';
            $confirmedAt = $item->isFree() ? now() : null;

            // Cria a inscrição
            $registration = AgendaRegistrationModel::create([
                'agenda_item_id' => $command->agendaItemId,
                'user_id'        => $command->userId,
                'status'         => $status,
                'confirmed_at'   => $confirmedAt,
                'notes'          => $command->notes,
            ]);

            // Incrementa vagas usadas via DB para evitar race condition com Eloquent
            DB::table('agenda_items')
                ->where('id', $command->agendaItemId)
                ->increment('slots_used');

            return $registration;
        });
    }
}
