<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ScheduledPostModel extends Model
{
    protected $table = 'scheduled_posts';

    protected $fillable = [
        'tenant_id',
        'social_account_id',
        'product_id',
        'caption',
        'image_url',
        'platform',
        'status',
        'publish_at',
        'published_at',
        'external_post_id',
        'error_message',
    ];

    protected $casts = [
        'publish_at'   => 'datetime',
        'published_at' => 'datetime',
    ];

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccountModel::class, 'social_account_id');
    }
}
