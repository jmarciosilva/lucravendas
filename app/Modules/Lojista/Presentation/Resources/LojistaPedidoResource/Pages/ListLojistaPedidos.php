<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource;
use Filament\Resources\Pages\ListRecords;

class ListLojistaPedidos extends ListRecords
{
    protected static string $resource = LojistaPedidoResource::class;
}
