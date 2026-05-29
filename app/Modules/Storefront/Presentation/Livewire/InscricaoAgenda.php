<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Agenda\Application\UseCases\RegisterForEvent\RegisterForEventCommand;
use App\Modules\Agenda\Application\UseCases\RegisterForEvent\RegisterForEventHandler;
use App\Modules\Agenda\Infrastructure\Models\AgendaRegistrationModel;
use Livewire\Component;

/**
 * Componente Livewire para inscrição em itens de agenda.
 *
 * Verifica se o usuário já está inscrito no mount e processa
 * a inscrição ao ser acionado, exibindo feedback de sucesso ou erro.
 */
final class InscricaoAgenda extends Component
{
    /** ID do item de agenda */
    public int $agendaItemId;

    /** Indica se o evento é gratuito */
    public bool $isFree;

    /** Preço em centavos (0 se gratuito) */
    public int $priceCentavos;

    /** Slug do tenant para links e contexto */
    public string $tenantSlug;

    /** Estado: se o usuário já está inscrito */
    public bool $inscrito = false;

    /** Mensagem de erro da operação */
    public string $erro = '';

    /**
     * Inicializa o componente verificando se o usuário já está inscrito.
     */
    public function mount(int $agendaItemId, bool $isFree, int $priceCentavos, string $tenantSlug): void
    {
        $this->agendaItemId  = $agendaItemId;
        $this->isFree        = $isFree;
        $this->priceCentavos = $priceCentavos;
        $this->tenantSlug    = $tenantSlug;

        // Verifica se o usuário autenticado já tem inscrição neste item
        if (auth()->check()) {
            $this->inscrito = AgendaRegistrationModel::where('agenda_item_id', $agendaItemId)
                ->where('user_id', auth()->id())
                ->exists();
        }
    }

    /**
     * Processa a inscrição do usuário autenticado no item de agenda.
     */
    public function inscrever(RegisterForEventHandler $handler): void
    {
        $this->erro = '';

        // Verifica autenticação antes de processar
        if (! auth()->check()) {
            $this->erro = 'Você precisa estar logado para se inscrever.';
            return;
        }

        try {
            // Recupera o tenant ID pelo contexto da storefront
            $tenantId = app('storefront.tenant')->id;

            $handler->handle(new RegisterForEventCommand(
                tenantId:     $tenantId,
                agendaItemId: $this->agendaItemId,
                userId:       auth()->id(),
            ));

            $this->inscrito = true;
        } catch (\RuntimeException $e) {
            // Erros de negócio: vagas esgotadas, já inscrito, etc.
            $this->erro = $e->getMessage();
        }
    }

    public function render()
    {
        return view('storefront.livewire.inscricao-agenda');
    }
}
