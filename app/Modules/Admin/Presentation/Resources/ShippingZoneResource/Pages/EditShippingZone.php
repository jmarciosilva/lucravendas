<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ShippingZoneResource\Pages;

use App\Modules\Admin\Presentation\Resources\ShippingZoneResource;
use Filament\Resources\Pages\EditRecord;

class EditShippingZone extends EditRecord
{
    protected static string $resource = ShippingZoneResource::class;
}
