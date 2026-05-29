<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\Entities;

use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Carbon\Carbon;
use RuntimeException;

final class ScheduledPost
{
    private function __construct(
        private readonly ?int    $id,
        private readonly string  $tenantId,
        private readonly int     $socialAccountId,
        private readonly ?int    $productId,
        private readonly string  $caption,
        private readonly string  $imageUrl,
        private readonly SocialPlatform $platform,
        private PostStatus       $status,
        private readonly Carbon  $publishAt,
        private ?Carbon          $publishedAt,
        private ?string          $externalPostId,
        private ?string          $errorMessage,
    ) {}

    public static function create(
        string        $tenantId,
        int           $socialAccountId,
        string        $caption,
        string        $imageUrl,
        SocialPlatform $platform,
        Carbon        $publishAt,
        ?int          $productId = null,
    ): self {
        return new self(
            id: null,
            tenantId: $tenantId,
            socialAccountId: $socialAccountId,
            productId: $productId,
            caption: $caption,
            imageUrl: $imageUrl,
            platform: $platform,
            status: PostStatus::pending(),
            publishAt: $publishAt,
            publishedAt: null,
            externalPostId: null,
            errorMessage: null,
        );
    }

    public static function restore(
        int           $id,
        string        $tenantId,
        int           $socialAccountId,
        ?int          $productId,
        string        $caption,
        string        $imageUrl,
        SocialPlatform $platform,
        PostStatus    $status,
        Carbon        $publishAt,
        ?Carbon       $publishedAt,
        ?string       $externalPostId,
        ?string       $errorMessage,
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            socialAccountId: $socialAccountId,
            productId: $productId,
            caption: $caption,
            imageUrl: $imageUrl,
            platform: $platform,
            status: $status,
            publishAt: $publishAt,
            publishedAt: $publishedAt,
            externalPostId: $externalPostId,
            errorMessage: $errorMessage,
        );
    }

    public function markPublished(string $externalPostId): void
    {
        if (! $this->status->isPending()) {
            throw new RuntimeException('Apenas posts pendentes podem ser marcados como publicados.');
        }

        $this->status         = PostStatus::fromString(PostStatus::PUBLISHED);
        $this->publishedAt    = Carbon::now();
        $this->externalPostId = $externalPostId;
        $this->errorMessage   = null;
    }

    public function markFailed(string $errorMessage): void
    {
        if (! $this->status->isPending()) {
            throw new RuntimeException('Apenas posts pendentes podem ser marcados como falhos.');
        }

        $this->status       = PostStatus::fromString(PostStatus::FAILED);
        $this->errorMessage = $errorMessage;
    }

    public function cancel(): void
    {
        if (! $this->status->isPending()) {
            throw new RuntimeException('Apenas posts pendentes podem ser cancelados.');
        }

        $this->status = PostStatus::fromString(PostStatus::CANCELLED);
    }

    public function id(): ?int                { return $this->id; }
    public function tenantId(): string        { return $this->tenantId; }
    public function socialAccountId(): int    { return $this->socialAccountId; }
    public function productId(): ?int         { return $this->productId; }
    public function caption(): string         { return $this->caption; }
    public function imageUrl(): string        { return $this->imageUrl; }
    public function platform(): SocialPlatform { return $this->platform; }
    public function status(): PostStatus      { return $this->status; }
    public function publishAt(): Carbon       { return $this->publishAt; }
    public function publishedAt(): ?Carbon    { return $this->publishedAt; }
    public function externalPostId(): ?string { return $this->externalPostId; }
    public function errorMessage(): ?string   { return $this->errorMessage; }
}
