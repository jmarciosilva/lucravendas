<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLojistaZonaFrete extends EditRecord
{
    protected static string $resource = LojistaZonaFreteResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
