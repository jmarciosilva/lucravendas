<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\CategoryResource\Pages;

use App\Modules\Admin\Presentation\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/** Página de edição de categoria no painel admin. */
class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
