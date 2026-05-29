<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\PublishAgendaItem;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Handler para publicar um item de agenda.
 *
 * Altera o status de draft para published, tornando o item visível na vitrine.
 */
final class PublishAgendaItemHandler
{
    /**
     * Publica o item de agenda identificado pelo ID e tenant.
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

        // Altera status para publicado
        $item->status = 'published';
        $item->save();

        return $item;
    }
}
