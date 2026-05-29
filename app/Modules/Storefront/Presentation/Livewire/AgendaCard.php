<?php

declare(strict_types=1);

namespace App\Modules\Storefront\Presentation\Livewire;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Livewire\Component;

/**
 * Componente Livewire de card para exibição resumida de itens de agenda.
 *
 * Carrega os dados do item no mount e expõe apenas as propriedades
 * necessárias para a exibição em listagem.
 */
final class AgendaCard extends Component
{
    /** ID do item de agenda a exibir */
    public int $agendaItemId;

    /** Slug do tenant para geração de links */
    public string $tenantSlug;

    // ─── Propriedades públicas para a view ────────────────────────────────────

    /** Título do item */
    public string $titulo = '';

    /** Tipo: evento, curso ou workshop */
    public string $tipo = '';

    /** Data e hora de início formatada */
    public string $startsAt = '';

    /** Vagas disponíveis (null = ilimitado) */
    public ?int $slotsDisponiveis = null;

    /** Se o item é gratuito */
    public bool $isFree = false;

    /** Preço em centavos */
    public int $priceCentavos = 0;

    /** Slug do item para geração do link de detalhe */
    public string $slug = '';

    /** URL da imagem de destaque (pode ser vazia) */
    public string $imagemUrl = '';

    /**
     * Carrega os dados do item de agenda no mount.
     */
    public function mount(int $agendaItemId, string $tenantSlug): void
    {
        $this->agendaItemId = $agendaItemId;
        $this->tenantSlug   = $tenantSlug;

        $item = AgendaItemModel::find($agendaItemId);

        if ($item !== null) {
            $this->titulo           = $item->title;
            $this->tipo             = $item->type;
            $this->startsAt         = $item->starts_at?->format('d/m/Y H:i') ?? '';
            $this->slotsDisponiveis = $item->slotsAvailable();
            $this->isFree           = $item->isFree();
            $this->priceCentavos    = $item->price_centavos;
            $this->slug             = $item->slug;
            $this->imagemUrl        = $item->featured_image_url ?? '';
        }
    }

    public function render()
    {
        return view('storefront.livewire.agenda-card');
    }
}
