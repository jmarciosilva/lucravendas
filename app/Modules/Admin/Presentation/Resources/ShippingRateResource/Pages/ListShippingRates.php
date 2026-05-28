<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages;

use App\Modules\Admin\Presentation\Resources\ShippingRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShippingRates extends ListRecords
{
    protected static string $resource = ShippingRateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
