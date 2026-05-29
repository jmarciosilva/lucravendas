<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaPostAgendadoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaPostAgendadoResource;
use Filament\Resources\Pages\ListRecords;

class ListLojistaPostsAgendados extends ListRecords
{
    protected static string $resource = LojistaPostAgendadoResource::class;
}
