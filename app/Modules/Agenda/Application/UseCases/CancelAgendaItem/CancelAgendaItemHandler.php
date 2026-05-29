<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\CancelAgendaItem;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Handler para cancelar um item de agenda.
 *
 * Altera o status para cancelled, impedindo novas inscrições.
 */
final class CancelAgendaItemHandler
{
    /**
     * Cancela o item de agenda identificado pelo ID e tenant.
     *
     * @param  int    $id       ID do item
     * @param  string $tenantId ID do tenant
     * @return AgendaItemModel
     *
     * @throws ModelNotFoundException
     */
    public function handle(int $id, string $tenantId): AgendaItemModel
    {
        $item = AgendaItemModel::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Altera status para cancelado
        $item->status = 'cancelled';
        $item->save();

        return $item;
    }
}
