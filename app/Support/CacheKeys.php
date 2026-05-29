<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Constantes e helpers centralizados para chaves de cache Redis.
 *
 * Mantém consistência entre leitura (Cache::remember) e invalidação (Cache::forget).
 */
final class CacheKeys
{
    // TTLs em segundos
    public const CATEGORIES_TTL = 300; // 5 minutos
    public const PRODUCTS_TTL   = 180; // 3 minutos
    public const SELLERS_TTL    = 300; // 5 minutos

    public static function categories(string $tenantId): string
    {
        return "categories:{$tenantId}";
    }

    public static function products(string $tenantId, string $queryHash = ''): string
    {
        return $queryHash
            ? "products:{$tenantId}:{$queryHash}"
            : "products:{$tenantId}";
    }

    public static function sellers(string $tenantId): string
    {
        return "sellers:{$tenantId}";
    }

    public static function seller(string $tenantId, string $slug): string
    {
        return "seller:{$tenantId}:{$slug}";
    }

    /** Gera hash compacto a partir dos parâmetros de query para cache de listagens */
    public static function hashQuery(array $params): string
    {
        ksort($params);
        return md5(http_build_query($params));
    }
}
