<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Infrastructure\Models;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent da tabela `sellers`.
 */
class SellerModel extends Model
{
    protected $table = 'sellers';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'tenant_id',
        'user_id',
        'commission_rate',
        'status',
        'bank_info',
        'description',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'bank_info'       => 'array',
        'commission_rate' => 'float',
    ];

    /** Produtos vinculados a este seller */
    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'seller_id');
    }

    /** Comissões geradas pelos produtos deste seller */
    public function commissions(): HasMany
    {
        return $this->hasMany(CommissionModel::class, 'seller_id');
    }

    /** Repasses realizados para este seller */
    public function payouts(): HasMany
    {
        return $this->hasMany(PayoutModel::class, 'seller_id');
    }
}
