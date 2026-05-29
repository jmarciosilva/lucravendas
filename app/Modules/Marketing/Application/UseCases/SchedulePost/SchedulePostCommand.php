<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\SchedulePost;

use Carbon\Carbon;

final class SchedulePostCommand
{
    public function __construct(
        public readonly string  $tenantId,
        public readonly int     $socialAccountId,
        public readonly string  $caption,
        public readonly string  $imageUrl,
        public readonly string  $platform,
        public readonly Carbon  $publishAt,
        public readonly ?int    $productId = null,
    ) {}
}
