<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\UserResource\Pages;

use App\Modules\Admin\Presentation\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
