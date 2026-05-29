<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages\CreateLojistaTarifaFrete;
use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages\EditLojistaTarifaFrete;
use App\Modules\Lojista\Presentation\Resources\LojistaTarifaFreteResource\Pages\ListLojistaTarifasFrete;
use App\Modules\Shipping\Infrastructure\Models\ShippingRateModel;
use App\Modules\Shipping\Infrastructure\Models\ShippingZoneModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaTarifaFreteResource extends Resource
{
    protected static ?string $model = ShippingRateModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Tarifas de Frete';

    protected static ?string $modelLabel = 'Tarifa de Frete';

    protected static ?string $pluralModelLabel = 'Tarifas de Frete';

    protected static ?string $navigationGroup = 'Frete';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('zone')
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        $tenantId = auth()->user()?->tenant_id ?? '';

        return $form->schema([
            Forms\Components\Select::make('zone_id')
                ->label('Zona de Frete')
                ->options(
                    ShippingZoneModel::where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->pluck('name', 'id')
                )
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->label('Nome da Tarifa')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('carrier')
                ->label('Transportadora')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('service_code')
                ->label('Código do Serviço')
                ->maxLength(50),

            Forms\Components\TextInput::make('base_price')
                ->label('Preço Base (R$)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100))
                ->formatStateUsing(fn ($state) => $state / 100),

            Forms\Components\TextInput::make('price_per_kg')
                ->label('Adicional por kg (R$)')
                ->numeric()
                ->default(0)
                ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100))
                ->formatStateUsing(fn ($state) => $state / 100),

            Forms\Components\TextInput::make('min_days')
                ->label('Prazo mínimo (dias)')
                ->required()
                ->numeric()
                ->default(1),

            Forms\Components\TextInput::make('max_days')
                ->label('Prazo máximo (dias)')
                ->required()
                ->numeric()
                ->default(7),

            Forms\Components\TextInput::make('free_shipping_threshold')
                ->label('Frete grátis a partir de (R$)')
                ->helperText('Deixe em branco para desabilitar.')
                ->numeric()
                ->nullable()
                ->dehydrateStateUsing(fn ($state) => $state ? (int) round((float) $state * 100) : null)
                ->formatStateUsing(fn ($state) => $state ? $state / 100 : null),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativa')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('carrier')
                    ->label('Transportadora'),

                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zona'),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Preço Base')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                Tables\Columns\TextColumn::make('free_shipping_threshold')
                    ->label('Frete grátis a partir de')
                    ->formatStateUsing(fn ($state) => $state
                        ? 'R$ ' . number_format($state / 100, 2, ',', '.')
                        : '—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLojistaTarifasFrete::route('/'),
            'create' => CreateLojistaTarifaFrete::route('/create'),
            'edit'   => EditLojistaTarifaFrete::route('/{record}/edit'),
        ];
    }
}
