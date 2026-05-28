<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages;

use App\Modules\Admin\Presentation\Resources\ShippingRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingRate extends CreateRecord
{
    protected static string $resource = ShippingRateResource::class;
}
