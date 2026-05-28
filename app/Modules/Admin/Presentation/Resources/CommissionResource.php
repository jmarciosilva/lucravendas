<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\CommissionResource\Pages\ListCommissions;
use App\Modules\Marketplace\Infrastructure\Models\CommissionModel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommissionResource extends Resource
{
    protected static ?string $model = CommissionModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Comissões';

    protected static ?string $modelLabel = 'Comissão';

    protected static ?string $pluralModelLabel = 'Comissões';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('seller.name')->label('Seller')->searchable()->sortable(),

                TextColumn::make('order_item_id')
                    ->label('Item do Pedido')
                    ->formatStateUsing(fn ($state) => "#{$state}"),

                TextColumn::make('gross_amount')
                    ->label('Valor Bruto')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('commission_amount')
                    ->label('Comissão Plataforma')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('net_amount')
                    ->label('Líquido Seller')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'paid'      => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'   => 'Pendente',
                        'paid'      => 'Pago',
                        'cancelled' => 'Cancelado',
                        default     => $state,
                    }),

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
                        'paid'      => 'Pago',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissions::route('/'),
        ];
    }
}
