<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listagem de itens de agenda no painel do lojista.
 */
class ListLojistaAgenda extends ListRecords
{
    protected static string $resource = LojistaAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botão para criar novo item de agenda
            Actions\CreateAction::make(),
        ];
    }
}
