<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\TenantResource\Pages\CreateTenant;
use App\Modules\Admin\Presentation\Resources\TenantResource\Pages\EditTenant;
use App\Modules\Admin\Presentation\Resources\TenantResource\Pages\ListTenants;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Resource Filament para gerenciamento de tenants (lojas) pelo admin LucraOne.
 */
class TenantResource extends Resource
{
    protected static ?string $model = TenantModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $navigationLabel = 'Lojas (Tenants)';

    protected static ?string $modelLabel = 'Loja';

    protected static ?string $pluralModelLabel = 'Lojas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nome da Loja')
                ->required()
                ->maxLength(255),

            TextInput::make('slug')
                ->label('Slug (subdomínio)')
                ->required()
                ->alphaDash()
                ->maxLength(63)
                ->helperText('Usado no subdomínio: slug.lucravendas.com.br'),

            Select::make('plan')
                ->label('Plano')
                ->options([
                    'free'       => 'Gratuito',
                    'starter'    => 'Starter',
                    'growth'     => 'Growth',
                    'enterprise' => 'Enterprise',
                ])
                ->required()
                ->default('free'),

            Select::make('status')
                ->label('Status')
                ->options([
                    'active'    => 'Ativo',
                    'suspended' => 'Suspenso',
                    'trial'     => 'Trial',
                    'cancelled' => 'Cancelado',
                ])
                ->required()
                ->default('active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable()
                    ->limit(8),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('plan')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free'       => 'gray',
                        'starter'    => 'info',
                        'growth'     => 'success',
                        'enterprise' => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'suspended' => 'danger',
                        'trial'     => 'warning',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label('Plano')
                    ->options([
                        'free'       => 'Gratuito',
                        'starter'    => 'Starter',
                        'growth'     => 'Growth',
                        'enterprise' => 'Enterprise',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active'    => 'Ativo',
                        'suspended' => 'Suspenso',
                        'trial'     => 'Trial',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<string, class-string> */
    public static function getPages(): array
    {
        return [
            'index'  => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit'   => EditTenant::route('/{record}/edit'),
        ];
    }
}
