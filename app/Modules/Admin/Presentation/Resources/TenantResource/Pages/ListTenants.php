<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\TenantResource\Pages;

use App\Modules\Admin\Presentation\Resources\TenantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
