<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Imports;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * Importa produtos a partir de planilha CSV/XLSX.
 *
 * Colunas esperadas: name, slug (opcional), price, description (opcional),
 * compare_price (opcional), sku (opcional), stock (opcional),
 * category_slug (opcional), status (opcional).
 *
 * Preços informados em REAIS (ex: 29.90) e convertidos para centavos internamente.
 * O tenant_id é injetado pelo seletor no formulário de upload.
 */
class ProductImporter extends Importer
{
    protected static ?string $model = ProductModel::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('slug')
                ->label('Slug')
                ->rules(['nullable', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/']),

            ImportColumn::make('price')
                ->label('Preço (em reais, ex: 29.90)')
                ->requiredMapping()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('description')
                ->label('Descrição')
                ->rules(['nullable', 'string']),

            ImportColumn::make('compare_price')
                ->label('Preço Original (em reais)')
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('sku')
                ->label('SKU')
                ->rules(['nullable', 'string', 'max:100']),

            ImportColumn::make('stock')
                ->label('Estoque')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('category_slug')
                ->label('Slug da Categoria')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('status')
                ->label('Status (draft/active/inactive)')
                ->rules(['nullable', 'in:draft,active,inactive']),
        ];
    }

    public function resolveRecord(): ?ProductModel
    {
        $tenantId = $this->options['tenant_id'];
        $slug     = $this->data['slug'] ?: Str::slug($this->data['name']);

        return ProductModel::firstOrNew([
            'slug'      => $slug,
            'tenant_id' => $tenantId,
        ]);
    }

    protected function beforeSave(): void
    {
        $tenantId = $this->options['tenant_id'];

        $this->record->tenant_id = $tenantId;
        $this->record->slug      = $this->data['slug'] ?: Str::slug($this->data['name']);
        $this->record->status    = $this->data['status'] ?: 'draft';
        $this->record->stock     = (int) ($this->data['stock'] ?? 0);

        // Converte preços de reais para centavos
        $this->record->price = (int) round((float) $this->data['price'] * 100);

        if (!empty($this->data['compare_price'])) {
            $this->record->compare_price = (int) round((float) $this->data['compare_price'] * 100);
        }

        // Resolve o slug da categoria para o category_id correspondente
        if (!empty($this->data['category_slug'])) {
            $category = CategoryModel::where('slug', $this->data['category_slug'])
                ->where('tenant_id', $tenantId)
                ->first();

            $this->record->category_id = $category?->id;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de produtos concluída: ' . number_format($import->successful_rows) . ' linha(s) importada(s)';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' — ' . number_format($failedRowsCount) . ' linha(s) com erro.';
        }

        return $body;
    }
}
