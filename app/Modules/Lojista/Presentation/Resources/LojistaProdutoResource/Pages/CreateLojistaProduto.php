<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaProdutoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLojistaProduto extends CreateRecord
{
    protected static string $resource = LojistaProdutoResource::class;

    /** Injeta o tenant_id do lojista autenticado antes de salvar */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
