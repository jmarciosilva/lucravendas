<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources;

use App\Modules\Admin\Presentation\Resources\PayoutResource\Pages\ListPayouts;
use App\Modules\Marketplace\Infrastructure\Models\PayoutModel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutResource extends Resource
{
    protected static ?string $model = PayoutModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Repasses';

    protected static ?string $modelLabel = 'Repasse';

    protected static ?string $pluralModelLabel = 'Repasses';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('seller.name')->label('Seller')->searchable()->sortable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state / 100, 2, ',', '.')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'       => 'success',
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'failed'     => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'    => 'Pendente',
                        'processing' => 'Processando',
                        'paid'       => 'Pago',
                        'failed'     => 'Falhou',
                        default      => $state,
                    }),

                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Pendente',
                        'processing' => 'Processando',
                        'paid'       => 'Pago',
                        'failed'     => 'Falhou',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
        ];
    }
}
