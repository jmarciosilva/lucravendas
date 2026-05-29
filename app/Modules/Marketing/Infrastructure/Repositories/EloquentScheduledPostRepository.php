<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Infrastructure\Repositories;

use App\Modules\Marketing\Domain\Entities\ScheduledPost;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use Carbon\Carbon;

final class EloquentScheduledPostRepository implements ScheduledPostRepositoryInterface
{
    public function save(ScheduledPost $post): ScheduledPost
    {
        $data = [
            'tenant_id'        => $post->tenantId(),
            'social_account_id' => $post->socialAccountId(),
            'product_id'       => $post->productId(),
            'caption'          => $post->caption(),
            'image_url'        => $post->imageUrl(),
            'platform'         => $post->platform()->value(),
            'status'           => $post->status()->value(),
            'publish_at'       => $post->publishAt(),
            'published_at'     => $post->publishedAt(),
            'external_post_id' => $post->externalPostId(),
            'error_message'    => $post->errorMessage(),
        ];

        if ($post->id() !== null) {
            ScheduledPostModel::where('id', $post->id())->update($data);
            $model = ScheduledPostModel::find($post->id());
        } else {
            $model = ScheduledPostModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function findById(int $id, string $tenantId): ?ScheduledPost
    {
        $model = ScheduledPostModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByTenant(string $tenantId): array
    {
        return ScheduledPostModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('publish_at', 'desc')
            ->get()
            ->map(fn (ScheduledPostModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findDuePosts(Carbon $until): array
    {
        return ScheduledPostModel::query()
            ->where('status', PostStatus::PENDING)
            ->where('publish_at', '<=', $until)
            ->orderBy('publish_at')
            ->get()
            ->map(fn (ScheduledPostModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(ScheduledPostModel $model): ScheduledPost
    {
        return ScheduledPost::restore(
            id: $model->id,
            tenantId: $model->tenant_id,
            socialAccountId: $model->social_account_id,
            productId: $model->product_id,
            caption: $model->caption,
            imageUrl: $model->image_url,
            platform: SocialPlatform::fromString($model->platform),
            status: PostStatus::fromString($model->status),
            publishAt: Carbon::parse($model->publish_at),
            publishedAt: $model->published_at ? Carbon::parse($model->published_at) : null,
            externalPostId: $model->external_post_id,
            errorMessage: $model->error_message,
        );
    }
}
