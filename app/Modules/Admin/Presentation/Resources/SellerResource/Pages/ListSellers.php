<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\SellerResource\Pages;

use App\Modules\Admin\Presentation\Resources\SellerResource;
use Filament\Resources\Pages\ListRecords;

class ListSellers extends ListRecords
{
    protected static string $resource = SellerResource::class;
}
