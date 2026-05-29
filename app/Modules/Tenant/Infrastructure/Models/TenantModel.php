<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Infrastructure\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenantModel;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

/**
 * Modelo Eloquent do Tenant — responsável exclusivamente por persistência.
 *
 * Estende o modelo base do stancl/tenancy para manter compatibilidade
 * com os bootstrappers de banco de dados e domínio do pacote.
 * Nenhuma lógica de negócio deve residir aqui.
 */
class TenantModel extends BaseTenantModel implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    protected $table = 'tenants';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'plan',
        'status',
        'data',
        'origin_zipcode',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'data' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static $customColumns = ['name', 'slug', 'plan', 'status', 'origin_zipcode'];

    /**
     * Retorna os nomes de colunas personalizadas do tenant.
     * Necessário para o stancl/tenancy diferenciar colunas reais de dados JSON.
     *
     * @return list<string>
     */
    public static function getCustomColumns(): array
    {
        return static::$customColumns;
    }
}
