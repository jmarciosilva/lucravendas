<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Application\UseCases\CreateAgendaItem;

use App\Modules\Agenda\Infrastructure\Models\AgendaItemModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Handler para criação de um item de agenda.
 *
 * Gera slug único dentro do tenant e cria o item com status=draft.
 */
final class CreateAgendaItemHandler
{
    /**
     * Cria um novo item de agenda em status rascunho.
     *
     * @param  CreateAgendaItemCommand $command
     * @return AgendaItemModel
     */
    public function handle(CreateAgendaItemCommand $command): AgendaItemModel
    {
        return DB::transaction(function () use ($command): AgendaItemModel {
            // Gera slug base a partir do título
            $slugBase = Str::slug($command->title);
            $slug     = $slugBase;
            $counter  = 1;

            // Garante unicidade do slug dentro do tenant
            while (AgendaItemModel::where('tenant_id', $command->tenantId)->where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$counter}";
                $counter++;
            }

            // Cria o item com status rascunho (aguardando publicação)
            return AgendaItemModel::create([
                'tenant_id'          => $command->tenantId,
                'seller_id'          => $command->sellerId,
                'type'               => $command->type,
                'title'              => $command->title,
                'slug'               => $slug,
                'description'        => $command->description,
                'short_description'  => $command->shortDescription,
                'featured_image_url' => $command->featuredImageUrl,
                'starts_at'          => $command->startsAt,
                'ends_at'            => $command->endsAt,
                'location'           => $command->location,
                'slots'              => $command->slots,
                'slots_used'         => 0,
                'price_centavos'     => $command->priceCentavos,
                'status'             => 'draft',
            ]);
        });
    }
}
