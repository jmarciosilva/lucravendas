<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLojistaTarifaFrete extends CreateRecord
{
    protected static string $resource = LojistaTarifaFreteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
