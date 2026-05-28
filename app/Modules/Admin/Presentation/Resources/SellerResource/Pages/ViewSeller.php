<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\SellerResource\Pages;

use App\Modules\Admin\Presentation\Resources\SellerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSeller extends ViewRecord
{
    protected static string $resource = SellerResource::class;
}
