<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLojistaCategoria extends ListRecords
{
    protected static string $resource = LojistaCategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
