<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\GetAgendaItem;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Handler para buscar o detalhe de um item de agenda pelo slug.
 *
 * Lança ModelNotFoundException se o item não existir, não pertencer
 * ao tenant informado, ou não estiver publicado.
 */
final class GetAgendaItemHandler
{
    /**
     * Retorna o item de agenda publicado pelo slug e tenant.
     *
     * @param  string $tenantId ID do tenant
     * @param  string $slug     Slug do item
     * @return AgendaItemModel
     *
     * @throws ModelNotFoundException
     */
    public function handle(string $tenantId, string $slug): AgendaItemModel
    {
        $item = AgendaItemModel::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('seller')
            ->first();

        if ($item === null) {
            throw new ModelNotFoundException("Item de agenda '{$slug}' não encontrado.");
        }

        return $item;
    }
}
