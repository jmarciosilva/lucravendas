<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages\CreateLojistaCupom;
use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages\EditLojistaCupom;
use App\Modules\Lojista\Presentation\Resources\LojistaCupomResource\Pages\ListLojistaCupons;
use App\Modules\Orders\Infrastructure\Models\CouponModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaCupomResource extends Resource
{
    protected static ?string $model = CouponModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Cupons';

    protected static ?string $modelLabel = 'Cupom';

    protected static ?string $pluralModelLabel = 'Cupons';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Código')
                ->required()
                ->maxLength(50)
                ->dehydrateStateUsing(fn ($state) => strtoupper((string) $state)),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'percent' => 'Percentual (%)',
                    'fixed'   => 'Valor fixo (R$)',
                ])
                ->required(),

            Forms\Components\TextInput::make('value')
                ->label('Valor (centavos ou %)')
                ->helperText('Para percentual, use o valor inteiro (ex: 10 = 10%). Para fixo, informe em centavos.')
                ->required()
                ->numeric()
                ->minValue(1),

            Forms\Components\TextInput::make('min_order_value')
                ->label('Pedido mínimo (centavos)')
                ->helperText('Valor mínimo do pedido para aplicar o cupom.')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('max_uses')
                ->label('Limite de usos')
                ->helperText('Deixe em branco para uso ilimitado.')
                ->numeric()
                ->nullable(),

            Forms\Components\DateTimePicker::make('expires_at')
                ->label('Expira em')
                ->nullable(),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state === 'percent' ? 'info' : 'primary')
                    ->formatStateUsing(fn ($state) => $state === 'percent' ? 'Percentual' : 'Fixo'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percent'
                        ? "{$state}%"
                        : 'R$ ' . number_format($state / 100, 2, ',', '.')),

                Tables\Columns\TextColumn::make('uses_count')
                    ->label('Usos')
                    ->formatStateUsing(fn ($state, $record) => $record->max_uses
                        ? "{$state}/{$record->max_uses}"
                        : $state),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y')
                    ->placeholder('Sem expiração'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
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
            'index'  => ListLojistaCupons::route('/'),
            'create' => CreateLojistaCupom::route('/create'),
            'edit'   => EditLojistaCupom::route('/{record}/edit'),
        ];
    }
}
