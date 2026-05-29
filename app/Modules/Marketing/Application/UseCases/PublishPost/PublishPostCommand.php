<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\PublishPost;

final class PublishPostCommand
{
    public function __construct(
        public readonly int $postId,
    ) {}
}
