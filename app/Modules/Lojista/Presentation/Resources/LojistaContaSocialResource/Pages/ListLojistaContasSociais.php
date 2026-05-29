<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaContaSocialResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaContaSocialResource;
use Filament\Resources\Pages\ListRecords;

class ListLojistaContasSociais extends ListRecords
{
    protected static string $resource = LojistaContaSocialResource::class;
}
