<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages;

use App\Modules\Admin\Presentation\Resources\ShippingRateResource;
use Filament\Resources\Pages\EditRecord;

class EditShippingRate extends EditRecord
{
    protected static string $resource = ShippingRateResource::class;
}
