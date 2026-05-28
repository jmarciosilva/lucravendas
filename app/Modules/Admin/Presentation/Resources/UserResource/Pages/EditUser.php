<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\UserResource\Pages;

use App\Modules\Admin\Presentation\Resources\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
