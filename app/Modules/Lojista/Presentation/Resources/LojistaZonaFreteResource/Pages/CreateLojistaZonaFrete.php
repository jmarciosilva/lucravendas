<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLojistaZonaFrete extends CreateRecord
{
    protected static string $resource = LojistaZonaFreteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->tenant_id;
        return $data;
    }
}
