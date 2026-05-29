<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\Repositories;

use App\Modules\Marketing\Domain\Entities\ScheduledPost;
use Carbon\Carbon;

interface ScheduledPostRepositoryInterface
{
    public function save(ScheduledPost $post): ScheduledPost;

    public function findById(int $id, string $tenantId): ?ScheduledPost;

    /** @return ScheduledPost[] */
    public function findAllByTenant(string $tenantId): array;

    /**
     * Retorna posts com status=pending e publish_at <= $until.
     * Usado pelo job de publicação horária.
     *
     * @return ScheduledPost[]
     */
    public function findDuePosts(Carbon $until): array;
}
