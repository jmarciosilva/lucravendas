<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\CategoryResource\Pages;

use App\Modules\Admin\Presentation\Resources\CategoryResource;
use App\Modules\Catalog\Application\Imports\CategoryImporter;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

/** Página de listagem de categorias no painel admin. */
class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ImportAction::make()
                ->importer(CategoryImporter::class)
                ->label('Importar Planilha')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar Categorias via Planilha')
                ->form([
                    Select::make('tenant_id')
                        ->label('Loja (Tenant)')
                        ->options(fn () => TenantModel::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Selecione a loja para a qual as categorias serão importadas.'),
                ]),
        ];
    }
}
