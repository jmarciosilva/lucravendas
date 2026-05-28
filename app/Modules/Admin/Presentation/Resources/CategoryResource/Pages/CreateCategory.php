<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\CategoryResource\Pages;

use App\Modules\Admin\Presentation\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

/** Página de criação de categoria no painel admin. */
class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
