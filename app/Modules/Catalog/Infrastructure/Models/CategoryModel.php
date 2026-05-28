<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent da tabela `categories`.
 *
 * Responsabilidade exclusiva: persistência e relacionamentos.
 * Toda lógica de negócio deve estar na entidade Category do domínio.
 */
class CategoryModel extends Model
{
    protected $table = 'categories';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'tenant_id',
        'sort_order',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'parent_id'  => 'integer',
    ];

    /** Categoria pai — null quando for categoria raiz */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'parent_id');
    }

    /** Subcategorias diretas desta categoria */
    public function children(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'parent_id')->orderBy('sort_order');
    }

    /** Produtos diretamente associados a esta categoria */
    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }
}
