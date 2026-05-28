<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ProductResource\Pages;

use App\Modules\Admin\Presentation\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

/** Página de criação de produto no painel admin. */
class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
