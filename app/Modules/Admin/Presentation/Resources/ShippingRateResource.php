<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages\CreateShippingRate;
use App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages\EditShippingRate;
use App\Modules\Admin\Presentation\Resources\ShippingRateResource\Pages\ListShippingRates;
use App\Modules\Shipping\Infrastructure\Models\ShippingRateModel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShippingRateResource extends Resource
{
    protected static ?string $model = ShippingRateModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Frete';

    protected static ?string $navigationLabel = 'Tarifas de Frete';

    protected static ?string $modelLabel = 'Tarifa';

    protected static ?string $pluralModelLabel = 'Tarifas de Frete';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('zone_id')
                ->label('Zona')
                ->relationship('zone', 'name')
                ->searchable()
                ->required(),
            TextInput::make('tenant_id')->label('Tenant ID')->required(),
            TextInput::make('name')->label('Nome da Tarifa')->required()->maxLength(100),
            Select::make('carrier')
                ->label('Transportadora')
                ->options([
                    'correios'     => 'Correios',
                    'melhorenvio'  => 'Melhor Envio',
                    'jadlog'       => 'Jadlog',
                    'custom'       => 'Personalizado',
                ])
                ->required(),
            TextInput::make('service_code')->label('Código do Serviço')->nullable(),
            TextInput::make('base_price')
                ->label('Preço Base (centavos)')
                ->numeric()
                ->required()
                ->helperText('Ex: 1500 = R$ 15,00'),
            TextInput::make('price_per_kg')
                ->label('Adicional por Kg (centavos)')
                ->numeric()
                ->default(0),
            TextInput::make('min_days')->label('Prazo Mínimo (dias)')->numeric()->default(1),
            TextInput::make('max_days')->label('Prazo Máximo (dias)')->numeric()->default(10),
            TextInput::make('free_shipping_threshold')
                ->label('Frete Grátis a partir de (centavos)')
                ->numeric()
                ->nullable()
                ->helperText('Deixe vazio para desativar. Ex: 10000 = R$ 100,00'),
            Toggle::make('is_active')->label('Ativo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('zone.name')->label('Zona')->sortable(),
                TextColumn::make('tenant_id')->label('Tenant'),
                TextColumn::make('name')->label('Tarifa')->searchable()->sortable(),
                TextColumn::make('carrier')->label('Transportadora')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'correios'    => 'info',
                        'melhorenvio' => 'success',
                        default       => 'gray',
                    }),
                TextColumn::make('base_price')
                    ->label('Preço Base')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),
                TextColumn::make('free_shipping_threshold')
                    ->label('Frete Grátis acima de')
                    ->formatStateUsing(fn ($state) => $state ? 'R$ ' . number_format($state / 100, 2, ',', '.') : '—'),
                TextColumn::make('min_days')
                    ->label('Prazo')
                    ->formatStateUsing(fn ($state, $record) => "{$record->min_days}–{$record->max_days} dias"),
                TextColumn::make('is_active')
                    ->label('Ativo')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
            ])
            ->filters([
                SelectFilter::make('carrier')
                    ->label('Transportadora')
                    ->options(['correios' => 'Correios', 'melhorenvio' => 'Melhor Envio', 'custom' => 'Personalizado']),
            ])
            ->defaultSort('zone_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListShippingRates::route('/'),
            'create' => CreateShippingRate::route('/create'),
            'edit'   => EditShippingRate::route('/{record}/edit'),
        ];
    }
}
