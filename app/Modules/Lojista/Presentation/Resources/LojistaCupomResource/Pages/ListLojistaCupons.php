<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLojistaCupons extends ListRecords
{
    protected static string $resource = LojistaCupomResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
