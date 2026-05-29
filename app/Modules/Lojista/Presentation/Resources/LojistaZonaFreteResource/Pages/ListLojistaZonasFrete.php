<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLojistaZonasFrete extends ListRecords
{
    protected static string $resource = LojistaZonaFreteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
