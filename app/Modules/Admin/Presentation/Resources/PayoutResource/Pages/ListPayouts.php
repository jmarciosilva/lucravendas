<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\PayoutResource\Pages;

use App\Modules\Admin\Presentation\Resources\PayoutResource;
use Filament\Resources\Pages\ListRecords;

class ListPayouts extends ListRecords
{
    protected static string $resource = PayoutResource::class;
}
