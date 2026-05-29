<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCategoriaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLojistaCategoria extends CreateRecord
{
    protected static string $resource = LojistaCategoriaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
