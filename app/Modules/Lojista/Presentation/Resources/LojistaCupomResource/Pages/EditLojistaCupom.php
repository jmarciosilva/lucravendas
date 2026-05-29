<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLojistaCupom extends EditRecord
{
    protected static string $resource = LojistaCupomResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
