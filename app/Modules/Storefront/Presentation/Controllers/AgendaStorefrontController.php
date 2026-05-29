<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Controllers;

use App\Modules\Agenda\Application\UseCases\GetAgendaItem\GetAgendaItemHandler;
use App\Modules\Agenda\Application\UseCases\ListAgendaItems\ListAgendaItemsHandler;
use App\Modules\Agenda\Infrastructure\Models\AgendaRegistrationModel;
use App\Support\StorefrontContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;

/**
 * Controller da vitrine para a seção de Agenda.
 *
 * Verifica a feature flag 'agenda' do tenant antes de exibir qualquer rota.
 * Caso a feature esteja desabilitada, retorna 404.
 */
final class AgendaStorefrontController
{
    public function __construct(
        private readonly ListAgendaItemsHandler $listHandler,
        private readonly GetAgendaItemHandler $getHandler,
    ) {}

    /**
     * Listagem de itens de agenda da loja.
     * Rota: GET /loja/{tenantSlug}/agenda
     */
    public function index(): View
    {
        $tenant = StorefrontContext::tenant();

        // Verifica se a feature de agenda está habilitada para este tenant
        if (! $tenant->feature('agenda')) {
            abort(404);
        }

        $sellerEventsOnMarketplace = $tenant->feature('seller_events_on_marketplace');

        $itens = $this->listHandler->handle(
            tenantId: $tenant->id,
            sellerEventsOnMarketplace: $sellerEventsOnMarketplace,
        );

        return view('storefront.agenda.index', compact('itens'));
    }

    /**
     * Detalhe de um item de agenda da loja.
     * Rota: GET /loja/{tenantSlug}/agenda/{slug}
     */
    public function show(string $tenantSlug, string $slug): View
    {
        $tenant = StorefrontContext::tenant();

        // Verifica se a feature de agenda está habilitada para este tenant
        if (! $tenant->feature('agenda')) {
            abort(404);
        }

        try {
            $item = $this->getHandler->handle($tenant->id, $slug);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        // Verifica se o usuário autenticado já está inscrito neste item
        $jaInscrito = auth()->check()
            ? AgendaRegistrationModel::where('agenda_item_id', $item->id)
                ->where('user_id', auth()->id())
                ->exists()
            : false;

        return view('storefront.agenda.show', compact('item', 'jaInscrito'));
    }
}
