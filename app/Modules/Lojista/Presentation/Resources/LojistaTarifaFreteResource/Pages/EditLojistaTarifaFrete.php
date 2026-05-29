<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLojistaTarifaFrete extends EditRecord
{
    protected static string $resource = LojistaTarifaFreteResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
