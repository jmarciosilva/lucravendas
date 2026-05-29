<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\SchedulePost;

use JsonSerializable;

final class SchedulePostOutput implements JsonSerializable
{
    public function __construct(
        public readonly int    $postId,
        public readonly string $status,
        public readonly string $publishAt,
        public readonly string $platform,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'post_id'    => $this->postId,
            'status'     => $this->status,
            'publish_at' => $this->publishAt,
            'platform'   => $this->platform,
        ];
    }
}
