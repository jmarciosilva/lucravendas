<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Widgets;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget do dashboard admin que exibe produtos com estoque crítico.
 *
 * Considera estoque baixo qualquer produto ativo com menos de 5 unidades.
 * Ajuste o threshold conforme a necessidade do negócio.
 */
class LowStockWidget extends BaseWidget
{
    protected static ?string $heading = 'Estoque Crítico (< 5 unidades)';

    protected static ?int $sort = 3;

    /** Ocupa a largura total da grade do dashboard */
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductModel::query()
                    ->where('status', 'active')
                    ->where('stock', '<', 5)
                    ->orderBy('stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tenant_id')
                    ->label('Tenant'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->default('—'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Estoque')
                    // Vermelho quando zerado, laranja quando baixo mas não zerado
                    ->color(fn ($state) => $state === 0 ? 'danger' : 'warning'),
            ])
            ->paginated(false);
    }
}
