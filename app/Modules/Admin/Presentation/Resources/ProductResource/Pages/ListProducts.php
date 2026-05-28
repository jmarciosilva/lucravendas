<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ProductResource\Pages;

use App\Modules\Admin\Presentation\Resources\ProductResource;
use App\Modules\Catalog\Application\Imports\ProductImporter;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

/** Página de listagem de produtos no painel admin. */
class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Importar Planilha')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar Produtos via Planilha')
                ->form([
                    Select::make('tenant_id')
                        ->label('Loja (Tenant)')
                        ->options(fn () => TenantModel::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('Selecione a loja para a qual os produtos serão importados.'),
                ]),
        ];
    }
}
