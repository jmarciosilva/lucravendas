<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\ListAgendaItems;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Illuminate\Support\Collection;

/**
 * Handler para listagem de itens de agenda publicados e futuros.
 *
 * Retorna apenas itens com status=published e starts_at >= now(),
 * ordenados por data de início crescente.
 */
final class ListAgendaItemsHandler
{
    /**
     * Lista itens de agenda disponíveis para um tenant.
     *
     * @param  string      $tenantId                    ID do tenant (loja)
     * @param  bool        $sellerEventsOnMarketplace   Se false, filtra apenas itens sem seller
     * @param  string|null $type                        Tipo opcional: evento, curso, workshop
     * @param  string|null $fromDate                    Data mínima de início (Y-m-d)
     * @param  bool        $apenasGratuitos             Se true, retorna apenas itens gratuitos
     * @return Collection<int, AgendaItemModel>
     */
    public function handle(
        string $tenantId,
        bool $sellerEventsOnMarketplace = true,
        ?string $type = null,
        ?string $fromDate = null,
        bool $apenasGratuitos = false,
    ): Collection {
        $query = AgendaItemModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'published')
            ->where('starts_at', '>=', now())
            ->with('seller');

        // Se a feature de eventos de sellers não está habilitada, filtra apenas itens do tenant
        if (! $sellerEventsOnMarketplace) {
            $query->whereNull('seller_id');
        }

        // Filtro opcional por tipo
        if ($type !== null) {
            $query->where('type', $type);
        }

        // Filtro opcional por data mínima
        if ($fromDate !== null) {
            $query->where('starts_at', '>=', $fromDate);
        }

        // Filtro de itens gratuitos
        if ($apenasGratuitos) {
            $query->where('price_centavos', 0);
        }

        return $query->orderBy('starts_at')->get();
    }
}
