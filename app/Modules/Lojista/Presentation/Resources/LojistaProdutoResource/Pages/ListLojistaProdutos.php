<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLojistaProdutos extends ListRecords
{
    protected static string $resource = LojistaProdutoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
