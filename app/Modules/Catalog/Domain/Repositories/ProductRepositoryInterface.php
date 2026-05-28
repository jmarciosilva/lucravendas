<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Repositories;

use App\Modules\Catalog\Domain\Entities\Product;

/**
 * Contrato do repositório de produtos.
 *
 * Todas as operações de leitura e escrita de produtos passam por aqui.
 * O domínio conhece apenas esta interface — nunca o Eloquent diretamente.
 */
interface ProductRepositoryInterface
{
    /** Persiste um produto novo ou atualiza um existente */
    public function save(Product $product): void;

    /** Busca produto por ID dentro de um tenant */
    public function findById(int $id, string $tenantId): ?Product;

    /** Busca produto por slug dentro de um tenant */
    public function findBySlug(string $slug, string $tenantId): ?Product;

    /**
     * Retorna produtos do tenant com suporte a filtros e paginação.
     *
     * @param  array<string, mixed>  $filters  Filtros aceitos: status, category_id, min_price, max_price
     * @return array{data: list<Product>, total: int, per_page: int, current_page: int}
     */
    public function paginate(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): array;

    /** Remove o produto com soft delete (preserva histórico de pedidos) */
    public function delete(int $id, string $tenantId): void;

    /** Verifica se já existe um produto com o slug informado neste tenant */
    public function existsBySlug(string $slug, string $tenantId, ?int $excludeId = null): bool;

    /**
     * Retorna os produtos com estoque abaixo do threshold.
     *
     * @return list<Product>
     */
    public function findLowStock(string $tenantId, int $threshold = 5): array;
}
