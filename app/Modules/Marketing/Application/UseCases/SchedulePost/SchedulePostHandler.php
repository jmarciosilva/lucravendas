<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\SchedulePost;

use App\Modules\Marketing\Domain\Entities\ScheduledPost;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use RuntimeException;

final class SchedulePostHandler
{
    public function __construct(
        private readonly ScheduledPostRepositoryInterface  $postRepository,
        private readonly SocialAccountRepositoryInterface  $accountRepository,
    ) {}

    public function handle(SchedulePostCommand $command): SchedulePostOutput
    {
        // Valida que a conta pertence ao tenant
        $account = $this->accountRepository->findById($command->socialAccountId, $command->tenantId);

        if (! $account || ! $account->isActive()) {
            throw new RuntimeException('Conta social não encontrada ou inativa para este tenant.');
        }

        $post = ScheduledPost::create(
            tenantId: $command->tenantId,
            socialAccountId: $command->socialAccountId,
            caption: $command->caption,
            imageUrl: $command->imageUrl,
            platform: SocialPlatform::fromString($command->platform),
            publishAt: $command->publishAt,
            productId: $command->productId,
        );

        $saved = $this->postRepository->save($post);

        return new SchedulePostOutput(
            postId: $saved->id(),
            status: $saved->status()->value(),
            publishAt: $saved->publishAt()->toISOString(),
            platform: $saved->platform()->value(),
        );
    }
}
