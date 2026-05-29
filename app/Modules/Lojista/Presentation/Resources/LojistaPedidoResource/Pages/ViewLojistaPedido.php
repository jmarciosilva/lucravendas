<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource\Pages;

use App\Modules\Lojista\Presentation\Resources\LojistaPedidoResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLojistaPedido extends ViewRecord
{
    protected static string $resource = LojistaPedidoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Resumo do Pedido')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('id')
                        ->label('Pedido #')
                        ->formatStateUsing(fn ($state) => "#{$state}"),

                    TextEntry::make('status')
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

                    TextEntry::make('payment_status')
                        ->label('Pagamento')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'paid'     => 'success',
                            'pending'  => 'warning',
                            'failed'   => 'danger',
                            'refunded' => 'gray',
                            default    => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'paid'     => 'Pago',
                            'pending'  => 'Pendente',
                            'failed'   => 'Falhou',
                            'refunded' => 'Estornado',
                            default    => $state,
                        }),

                    TextEntry::make('created_at')
                        ->label('Data')
                        ->dateTime('d/m/Y H:i'),
                ]),
            ]),

            Section::make('Valores')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('subtotal')
                        ->label('Subtotal')
                        ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                    TextEntry::make('discount')
                        ->label('Desconto')
                        ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                    TextEntry::make('shipping_cost')
                        ->label('Frete')
                        ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                    TextEntry::make('total')
                        ->label('Total')
                        ->weight('bold')
                        ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),
                ]),
            ]),

            Section::make('Itens do Pedido')->schema([
                RepeatableEntry::make('items')->schema([
                    Grid::make(4)->schema([
                        TextEntry::make('name')->label('Produto'),
                        TextEntry::make('sku')->label('SKU')->placeholder('—'),
                        TextEntry::make('quantity')->label('Qtd'),
                        TextEntry::make('unit_price')
                            ->label('Preço Unit.')
                            ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),
                    ]),
                ])->label(''),
            ]),

            Section::make('Endereço de Entrega')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('recipient_name')->label('Destinatário'),
                    TextEntry::make('recipient_zipcode')->label('CEP'),
                    TextEntry::make('recipient_city')->label('Cidade'),
                    TextEntry::make('recipient_state')->label('UF'),
                    TextEntry::make('recipient_address')->label('Endereço'),
                    TextEntry::make('recipient_number')->label('Número'),
                    TextEntry::make('recipient_complement')->label('Complemento')->placeholder('—'),
                ]),
            ]),

            Section::make('Rastreio')->schema([
                Grid::make(2)->schema([
                    TextEntry::make('tracking_code')
                        ->label('Código de Rastreio')
                        ->placeholder('Não gerado'),

                    TextEntry::make('tracking_status')
                        ->label('Status do Rastreio')
                        ->placeholder('—'),

                    TextEntry::make('shipping_label_url')
                        ->label('Etiqueta de Envio')
                        ->url(fn ($state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('Não gerada'),
                ]),
            ])->collapsed(),
        ]);
    }
}
