<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources;

use App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource\Pages\ListLojistaPedidos;
use App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource\Pages\ViewLojistaPedido;
use App\Modules\Orders\Infrastructure\Models\OrderModel;
use App\Modules\Orders\Infrastructure\Models\OrderStatusHistoryModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LojistaPedidoResource extends Resource
{
    protected static ?string $model = OrderModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Pedidos';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'items'])
            ->where('tenant_id', auth()->user()?->tenant_id ?? '');
    }

    public static function form(Form $form): Form
    {
        // Pedidos são somente leitura — criados pela API
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Pedido #')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "#{$state}"),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'processing' => 'primary',
                        'shipped'    => 'success',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'    => 'Pendente',
                        'confirmed'  => 'Confirmado',
                        'processing' => 'Em Processamento',
                        'shipped'    => 'Enviado',
                        'delivered'  => 'Entregue',
                        'cancelled'  => 'Cancelado',
                        default      => $state,
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        'refunded' => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid'    => 'Pago',
                        'pending' => 'Pendente',
                        'failed'  => 'Falhou',
                        'refunded' => 'Estornado',
                        default   => $state,
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Pendente',
                        'confirmed'  => 'Confirmado',
                        'processing' => 'Em Processamento',
                        'shipped'    => 'Enviado',
                        'delivered'  => 'Entregue',
                        'cancelled'  => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pagamento')
                    ->options([
                        'paid'    => 'Pago',
                        'pending' => 'Pendente',
                        'failed'  => 'Falhou',
                        'refunded' => 'Estornado',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Período')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('De'),
                        Forms\Components\DatePicker::make('until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (OrderModel $record) => $record->status === 'pending')
                    ->action(fn (OrderModel $record) => self::transicionarStatus($record, 'confirmed', 'Pedido confirmado.')),

                Tables\Actions\Action::make('processar')
                    ->label('Processar')
                    ->icon('heroicon-o-cog')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (OrderModel $record) => $record->status === 'confirmed')
                    ->action(fn (OrderModel $record) => self::transicionarStatus($record, 'processing', 'Pedido em processamento.')),

                Tables\Actions\Action::make('enviar')
                    ->label('Enviar')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (OrderModel $record) => $record->status === 'processing')
                    ->form([
                        Forms\Components\TextInput::make('tracking_code')
                            ->label('Código de Rastreio')
                            ->required(),
                    ])
                    ->action(function (OrderModel $record, array $data): void {
                        $record->update([
                            'status'       => 'shipped',
                            'tracking_code' => $data['tracking_code'],
                        ]);
                        OrderStatusHistoryModel::create([
                            'order_id' => $record->id,
                            'status'   => 'shipped',
                            'notes'    => "Código de rastreio: {$data['tracking_code']}",
                        ]);
                        Notification::make()->title('Pedido marcado como enviado.')->success()->send();
                    }),

                Tables\Actions\Action::make('entregar')
                    ->label('Entregue')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (OrderModel $record) => $record->status === 'shipped')
                    ->action(fn (OrderModel $record) => self::transicionarStatus($record, 'delivered', 'Pedido entregue ao cliente.')),

                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar pedido')
                    ->modalDescription('Esta ação cancela o pedido. O estoque NÃO é restaurado automaticamente.')
                    ->visible(fn (OrderModel $record) => in_array($record->status, ['pending', 'confirmed', 'processing']))
                    ->action(fn (OrderModel $record) => self::transicionarStatus($record, 'cancelled', 'Pedido cancelado pelo lojista.')),

                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function transicionarStatus(OrderModel $order, string $novoStatus, string $mensagem): void
    {
        $order->update(['status' => $novoStatus]);
        OrderStatusHistoryModel::create([
            'order_id' => $order->id,
            'status'   => $novoStatus,
        ]);
        Notification::make()->title($mensagem)->success()->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLojistaPedidos::route('/'),
            'view'  => ViewLojistaPedido::route('/{record}'),
        ];
    }
}
