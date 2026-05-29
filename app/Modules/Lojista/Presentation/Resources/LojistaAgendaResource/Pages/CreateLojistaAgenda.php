<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de criação de item de agenda no painel do lojista.
 */
class CreateLojistaAgenda extends CreateRecord
{
    protected static string $resource = LojistaAgendaResource::class;

    /** Injeta tenant_id e status=draft antes de salvar o novo registro */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return LojistaAgendaResource::mutateFormDataBeforeCreate($data);
    }
}
