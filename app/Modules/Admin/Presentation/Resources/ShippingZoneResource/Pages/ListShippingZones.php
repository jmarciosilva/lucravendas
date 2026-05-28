<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ShippingZoneResource\Pages;

use App\Modules\Admin\Presentation\Resources\ShippingZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingZones extends ListRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
