<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Application\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

/**
 * Importa usuários a partir de planilha CSV/XLSX.
 *
 * Colunas esperadas: name, email, password, phone (opcional), role (opcional).
 * A senha é hasheada automaticamente pelo cast 'hashed' do model User.
 *
 * Se um e-mail já existir no banco, o registro é atualizado (upsert por e-mail).
 * O tenant_id é injetado pelo seletor no formulário de upload (opcional).
 * Deixar tenant_id vazio cria usuários sem tenant (ex: super_admins).
 */
class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nome Completo')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->label('E-mail')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('password')
                ->label('Senha')
                ->requiredMapping()
                ->rules(['required', 'string', 'min:8']),

            ImportColumn::make('phone')
                ->label('Telefone')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('role')
                ->label('Role (customer/tenant_admin/super_admin)')
                ->rules(['nullable', 'string', 'max:50']),
        ];
    }

    public function resolveRecord(): ?User
    {
        return User::firstOrNew(['email' => $this->data['email']]);
    }

    protected function beforeSave(): void
    {
        $tenantId = $this->options['tenant_id'] ?: null;

        $this->record->tenant_id = $tenantId;
        $this->record->status    = 'active';
    }

    protected function afterSave(): void
    {
        // Atribui a role — default 'customer' se não informado
        $role = !empty($this->data['role']) ? $this->data['role'] : 'customer';

        $this->record->syncRoles([$role]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importação de usuários concluída: ' . number_format($import->successful_rows) . ' linha(s) importada(s)';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' — ' . number_format($failedRowsCount) . ' linha(s) com erro.';
        }

        return $body;
    }
}
