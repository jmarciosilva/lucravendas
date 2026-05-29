<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaAgendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edição de item de agenda no painel do lojista.
 */
class EditLojistaAgenda extends EditRecord
{
    protected static string $resource = LojistaAgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botão de exclusão com confirmação
            Actions\DeleteAction::make(),
        ];
    }
}
