<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SocialAccountModel extends Model
{
    protected $table = 'social_accounts';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'platform',
        'account_id',
        'account_name',
        'access_token',
        'token_expires_at',
        'page_id',
        'instagram_account_id',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    public function scheduledPosts(): HasMany
    {
        return $this->hasMany(ScheduledPostModel::class, 'social_account_id');
    }
}
