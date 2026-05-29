<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\PublishPost;

use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use RuntimeException;

final class PublishPostHandler
{
    public function __construct(
        private readonly ScheduledPostRepositoryInterface  $postRepository,
        private readonly SocialAccountRepositoryInterface  $accountRepository,
        private readonly SocialGatewayInterface            $gateway,
    ) {}

    /**
     * Publica o post no gateway e atualiza o status.
     * Lança RuntimeException em caso de falha (o job captura e marca como failed).
     */
    public function handle(PublishPostCommand $command): void
    {
        // Busca em todos os tenants (chamado pelo job sem contexto de tenant)
        $model = \App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel::find($command->postId);

        if (! $model) {
            throw new RuntimeException("Post #{$command->postId} não encontrado.");
        }

        $post = $this->postRepository->findById($model->id, $model->tenant_id);

        if (! $post) {
            throw new RuntimeException("Post #{$command->postId} não encontrado no repositório.");
        }

        if (! $post->status()->isPending()) {
            return; // Idempotência: ignora se já processado
        }

        $account = $this->accountRepository->findById($post->socialAccountId(), $post->tenantId());

        if (! $account || ! $account->isActive()) {
            throw new RuntimeException("Conta social #{$post->socialAccountId()} inativa ou não encontrada.");
        }

        $externalPostId = $this->gateway->publishPost($account, $post->caption(), $post->imageUrl());

        $post->markPublished($externalPostId);
        $this->postRepository->save($post);
    }
}
