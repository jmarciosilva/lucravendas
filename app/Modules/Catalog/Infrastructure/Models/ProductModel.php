<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Models;

use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Modelo Eloquent da tabela `products`.
 *
 * Integra três concerns de infraestrutura:
 * - SoftDeletes: exclusão lógica para preservar histórico de pedidos
 * - Searchable (Scout): indexação automática no Meilisearch
 * - HasMedia (Spatie): gerenciamento de imagens via media library
 */
class ProductModel extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Searchable;
    use SoftDeletes;

    protected $table = 'products';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'sku',
        'stock',
        'status',
        'category_id',
        'tenant_id',
        'seller_id',
        'weight_grams',
        'length_cm',
        'width_cm',
        'height_cm',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'price'         => 'integer',
        'compare_price' => 'integer',
        'stock'         => 'integer',
        'weight_grams'  => 'integer',
        'length_cm'     => 'integer',
        'width_cm'      => 'integer',
        'height_cm'     => 'integer',
        'deleted_at'    => 'datetime',
    ];

    /** Categoria à qual o produto pertence */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    /** Seller responsável por este produto (nullable para produtos do próprio tenant) */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerModel::class, 'seller_id');
    }

    /** Variantes do produto (tamanho, cor, etc.) */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariantModel::class, 'product_id');
    }

    /**
     * Configura as coleções de mídia do produto.
     *
     * A coleção 'images' aceita apenas imagens e gera automaticamente
     * uma miniatura (thumb) de 300x300 pixels para listagens.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    /**
     * Configura as conversões de imagem aplicadas automaticamente após upload.
     * A conversão 'thumb' é usada em listagens e cards de produto.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();
    }

    /**
     * Define quais campos são indexados no Meilisearch.
     * Retorna apenas dados relevantes para busca — nunca dados sensíveis.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'sku'         => $this->sku,
            'slug'        => $this->slug,
            'status'      => $this->status,
            'tenant_id'   => $this->tenant_id,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'category_id' => $this->category_id,
        ];
    }

    /**
     * Filtra a indexação — apenas produtos ativos são indexados no Meilisearch.
     * Produtos em rascunho ou inativos não aparecem nos resultados de busca.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active';
    }
}
