<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\TenantResource\Pages;

use App\Modules\Admin\Presentation\Resources\TenantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
