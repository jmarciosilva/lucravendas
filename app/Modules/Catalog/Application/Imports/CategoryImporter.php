<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Imports;

use App\Modules\Catalog\Infrastructure\Models\CategoryModel;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * Importa categorias a partir de planilha CSV/XLSX.
 *
 * Colunas esperadas: name, slug (opcional), parent_slug (opcional),
 * sort_order (opcional), is_active (opcional).
 * O tenant_id é injetado pelo seletor no formulário de upload, nunca pela planilha.
 */
class CategoryImporter extends Importer
{
    protected static ?string $model = CategoryModel::class;

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

            ImportColumn::make('parent_slug')
                ->label('Slug da Categoria Pai')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('sort_order')
                ->label('Ordem de Exibição')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0']),

            ImportColumn::make('is_active')
                ->label('Ativa (true/false)')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?CategoryModel
    {
        $tenantId = $this->options['tenant_id'];
        $slug     = $this->data['slug'] ?: Str::slug($this->data['name']);

        return CategoryModel::firstOrNew([
            'slug'      => $slug,
            'tenant_id' => $tenantId,
        ]);
    }

    protected function beforeSave(): void
    {
        $tenantId = $this->options['tenant_id'];

        $this->record->tenant_id  = $tenantId;
        $this->record->slug       = $this->data['slug'] ?: Str::slug($this->data['name']);
        $this->record->sort_order = (int) ($this->data['sort_order'] ?? 0);
        $this->record->is_active  = isset($this->data['is_active'])
            ? (bool) $this->data['is_active']
            : true;

        // Resolve o slug da categoria pai para o parent_id correspondente
        if (!empty($this->data['parent_slug'])) {
            $parent = CategoryModel::where('slug', $this->data['parent_slug'])
                ->where('tenant_id', $tenantId)
                ->first();

            $this->record->parent_id = $parent?->id;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de categorias concluída: ' . number_format($import->successful_rows) . ' linha(s) importada(s)';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' — ' . number_format($failedRowsCount) . ' linha(s) com erro.';
        }

        return $body;
    }
}
