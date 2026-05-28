<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\PaymentTransactionResource\Pages\ListPaymentTransactions;
use App\Modules\Admin\Presentation\Resources\PaymentTransactionResource\Pages\ViewPaymentTransaction;
use App\Modules\Payments\Infrastructure\Models\PaymentTransactionModel;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransactionModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?string $navigationLabel = 'Pagamentos';

    protected static ?string $modelLabel = 'Transação';

    protected static ?string $pluralModelLabel = 'Transações';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('order_id')->label('Pedido #')->disabled(),
            TextInput::make('method')->label('Método')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
            TextInput::make('amount')
                ->label('Valor')
                ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                ->disabled(),
            TextInput::make('external_id')->label('ID no Mercado Pago')->disabled(),
            TextInput::make('ticket_url')->label('URL do Boleto')->disabled(),
            KeyValue::make('payload')->label('Payload completo')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('order_id')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "#{$state}"),

                TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pix'         => 'success',
                        'credit_card' => 'info',
                        'boleto'      => 'warning',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão',
                        'boleto'      => 'Boleto',
                        default       => $state,
                    }),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected',
                        'cancelled' => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved'  => 'Aprovado',
                        'pending'   => 'Pendente',
                        'rejected'  => 'Recusado',
                        'cancelled' => 'Cancelado',
                        'refunded'  => 'Estornado',
                        default     => $state,
                    }),

                TextColumn::make('installments')
                    ->label('Parcelas')
                    ->formatStateUsing(fn ($state) => $state > 1 ? "{$state}x" : 'À vista'),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pendente',
                        'approved'  => 'Aprovado',
                        'rejected'  => 'Recusado',
                        'cancelled' => 'Cancelado',
                        'refunded'  => 'Estornado',
                    ]),

                SelectFilter::make('method')
                    ->label('Método')
                    ->options([
                        'pix'         => 'PIX',
                        'credit_card' => 'Cartão de Crédito',
                        'boleto'      => 'Boleto',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentTransactions::route('/'),
            'view'  => ViewPaymentTransaction::route('/{record}'),
        ];
    }
}
