<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages\CreateLojistaZonaFrete;
use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages\EditLojistaZonaFrete;
use App\Modules\Lojista\Presentation\Resources\LojistaZonaFreteResource\Pages\ListLojistaZonasFrete;
use App\Modules\Shipping\Infrastructure\Models\ShippingZoneModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaZonaFreteResource extends Resource
{
    protected static ?string $model = ShippingZoneModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Zonas de Frete';

    protected static ?string $modelLabel = 'Zona de Frete';

    protected static ?string $pluralModelLabel = 'Zonas de Frete';

    protected static ?string $navigationGroup = 'Frete';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        $estados = [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
            'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
            'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
            'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
            'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
        ];

        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome da Zona')
                ->required()
                ->maxLength(100),

            Forms\Components\CheckboxList::make('states')
                ->label('Estados cobertos')
                ->options($estados)
                ->columns(4)
                ->gridDirection('row'),

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

                Tables\Columns\TextColumn::make('states')
                    ->label('Estados')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

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
            'index'  => ListLojistaZonasFrete::route('/'),
            'create' => CreateLojistaZonaFrete::route('/create'),
            'edit'   => EditLojistaZonaFrete::route('/{record}/edit'),
        ];
    }
}
