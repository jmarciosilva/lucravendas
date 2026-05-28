<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\CommissionResource\Pages;

use App\Modules\Admin\Presentation\Resources\CommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;
}
