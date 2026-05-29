<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLojistaTarifasFrete extends ListRecords
{
    protected static string $resource = LojistaTarifaFreteResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
