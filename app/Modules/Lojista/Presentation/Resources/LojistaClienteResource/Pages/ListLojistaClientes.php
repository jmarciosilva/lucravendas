<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaClienteResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaClienteResource;
use Filament\Resources\Pages\ListRecords;

class ListLojistaClientes extends ListRecords
{
    protected static string $resource = LojistaClienteResource::class;
}
