<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\UserResource\Pages;

use App\Modules\Admin\Presentation\Resources\UserResource;
use App\Modules\Tenant\Application\Imports\UserImporter;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(UserImporter::class)
                ->label('Importar Planilha')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importar Usuários via Planilha')
                ->form([
                    Select::make('tenant_id')
                        ->label('Loja (opcional)')
                        ->options(fn () => TenantModel::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->helperText('Deixe vazio para criar usuários sem loja (ex: super_admins).'),
                ]),
        ];
    }
}
