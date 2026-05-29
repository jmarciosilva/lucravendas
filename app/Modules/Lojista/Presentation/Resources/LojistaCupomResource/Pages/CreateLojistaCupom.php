<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLojistaCupom extends CreateRecord
{
    protected static string $resource = LojistaCupomResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
