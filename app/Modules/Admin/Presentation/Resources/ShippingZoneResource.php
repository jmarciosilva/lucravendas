<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\ShippingZoneResource\Pages\CreateShippingZone;
use App\Modules\Admin\Presentation\Resources\ShippingZoneResource\Pages\EditShippingZone;
use App\Modules\Admin\Presentation\Resources\ShippingZoneResource\Pages\ListShippingZones;
use App\Modules\Shipping\Infrastructure\Models\ShippingZoneModel;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZoneModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Frete';

    protected static ?string $navigationLabel = 'Zonas de Frete';

    protected static ?string $modelLabel = 'Zona';

    protected static ?string $pluralModelLabel = 'Zonas de Frete';

    protected static ?int $navigationSort = 1;

    private static function brazilianStates(): array
    {
        return [
            'AC' => 'AC — Acre',          'AL' => 'AL — Alagoas',
            'AM' => 'AM — Amazonas',       'AP' => 'AP — Amapá',
            'BA' => 'BA — Bahia',          'CE' => 'CE — Ceará',
            'DF' => 'DF — Distrito Federal','ES' => 'ES — Espírito Santo',
            'GO' => 'GO — Goiás',          'MA' => 'MA — Maranhão',
            'MG' => 'MG — Minas Gerais',   'MS' => 'MS — Mato Grosso do Sul',
            'MT' => 'MT — Mato Grosso',    'PA' => 'PA — Pará',
            'PB' => 'PB — Paraíba',        'PE' => 'PE — Pernambuco',
            'PI' => 'PI — Piauí',          'PR' => 'PR — Paraná',
            'RJ' => 'RJ — Rio de Janeiro', 'RN' => 'RN — Rio Grande do Norte',
            'RO' => 'RO — Rondônia',       'RR' => 'RR — Roraima',
            'RS' => 'RS — Rio Grande do Sul','SC' => 'SC — Santa Catarina',
            'SE' => 'SE — Sergipe',        'SP' => 'SP — São Paulo',
            'TO' => 'TO — Tocantins',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('tenant_id')->label('Tenant ID')->required(),
            TextInput::make('name')->label('Nome da Zona')->required()->maxLength(100),
            CheckboxList::make('states')
                ->label('Estados cobertos')
                ->options(self::brazilianStates())
                ->columns(4)
                ->required(),
            Toggle::make('is_active')->label('Ativo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('tenant_id')->label('Tenant'),
                TextColumn::make('name')->label('Zona')->searchable()->sortable(),
                TextColumn::make('states')
                    ->label('Estados')
                    ->formatStateUsing(fn ($state) => implode(', ', is_array($state) ? $state : json_decode($state, true) ?? [])),
                TextColumn::make('is_active')
                    ->label('Ativo')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não'),
                TextColumn::make('rates_count')
                    ->label('Tarifas')
                    ->counts('rates'),
            ])
            ->defaultSort('tenant_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListShippingZones::route('/'),
            'create' => CreateShippingZone::route('/create'),
            'edit'   => EditShippingZone::route('/{record}/edit'),
        ];
    }
}
