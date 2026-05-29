<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLojistaCategoria extends EditRecord
{
    protected static string $resource = LojistaCategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
