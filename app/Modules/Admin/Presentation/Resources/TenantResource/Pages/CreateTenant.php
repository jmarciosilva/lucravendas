<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\TenantResource\Pages;

use App\Modules\Admin\Presentation\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}
