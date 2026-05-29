<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLojistaProduto extends EditRecord
{
    protected static string $resource = LojistaProdutoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
