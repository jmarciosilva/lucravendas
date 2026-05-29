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
        'profile',
        'data',
        'origin_zipcode',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'data'       => 'array',
        'deleted_at' => 'datetime',
    ];

    protected static $customColumns = ['name', 'slug', 'plan', 'status', 'profile', 'origin_zipcode'];

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

    // ─── Helpers de perfil, tema e feature flags ──────────────────────────────

    /** Perfil do tenant (ex: 'esoterismo', 'loja_roupas', 'generico'). */
    public function profile(): string
    {
        return $this->profile ?? 'generico';
    }

    /**
     * Dados extras do tenant decodificados do campo JSON `data`.
     *
     * stancl/tenancy sobrescreve o accessor padrão do campo `data`, fazendo
     * $this->data retornar NULL. Usamos getRawOriginal() para ler o JSON bruto
     * diretamente do atributo original sem passar pelo accessor do stancl.
     *
     * @return array<string, mixed>
     */
    public function tenantData(): array
    {
        return json_decode($this->getRawOriginal('data') ?? '{}', true) ?? [];
    }

    /**
     * Persiste novos valores no campo JSON `data` mesclando com os existentes.
     * Usa DB::table para contornar a serialização customizada do stancl.
     *
     * @param array<string, mixed> $newData
     */
    public function saveTenantData(array $newData): void
    {
        $merged = array_merge($this->tenantData(), $newData);
        \Illuminate\Support\Facades\DB::table('tenants')
            ->where('id', $this->id)
            ->update(['data' => json_encode($merged)]);
    }

    /**
     * Tema visual ativo.
     * Prioridade: tenant.data['theme'] > padrão do profile > 'generico'.
     */
    public function theme(): string
    {
        return $this->tenantData()['theme']
            ?? config("storefront.profiles.{$this->profile()}.theme", 'generico');
    }

    /**
     * Verifica se uma feature está habilitada para este tenant.
     * Prioridade: valor explícito em tenant.data['features'] > padrão do profile.
     */
    public function feature(string $key): bool
    {
        $explicit = $this->tenantData()['features'][$key] ?? null;

        if ($explicit !== null) {
            return (bool) $explicit;
        }

        return (bool) config("storefront.profiles.{$this->profile()}.features.{$key}", false);
    }

    /** Atalho: verifica se o tenant opera como marketplace multi-seller. */
    public function isMarketplace(): bool
    {
        return $this->feature('marketplace');
    }
}
