<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ProductResource\Pages;

use App\Modules\Admin\Presentation\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Página de edição de produto no painel admin. */
class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
