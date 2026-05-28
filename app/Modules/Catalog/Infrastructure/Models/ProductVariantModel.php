<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent da tabela `product_variants`.
 *
 * Representa uma combinação específica de atributos de um produto
 * (ex.: Camiseta Azul / Tamanho M) com estoque e preço próprios.
 */
class ProductVariantModel extends Model
{
    protected $table = 'product_variants';

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'stock',
        'attributes',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'price'      => 'integer',
        'stock'      => 'integer',
        'is_active'  => 'boolean',
        'attributes' => 'array',
    ];

    /** Produto pai desta variante */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
