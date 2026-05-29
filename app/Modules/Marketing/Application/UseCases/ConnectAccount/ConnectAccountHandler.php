<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\ConnectAccount;

use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\Entities\SocialAccount;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Carbon\Carbon;

final class ConnectAccountHandler
{
    public function __construct(
        private readonly SocialGatewayInterface          $gateway,
        private readonly SocialAccountRepositoryInterface $repository,
    ) {}

    public function handle(ConnectAccountCommand $command): ConnectAccountOutput
    {
        $platform = SocialPlatform::fromString($command->platform);

        // Troca código OAuth por token de longa duração
        $tokenData = $this->gateway->exchangeCodeForToken($command->code);

        // Busca informações da conta (page_id, instagram_account_id, nome)
        $info = $this->gateway->getAccountInfo($tokenData['access_token'], $command->platform);

        $expiresAt = isset($tokenData['expires_in'])
            ? Carbon::now()->addSeconds($tokenData['expires_in'])
            : null;

        $account = SocialAccount::create(
            tenantId: $command->tenantId,
            userId: $command->userId,
            platform: $platform,
            accountId: $info['account_id'],
            accountName: $info['account_name'],
            accessToken: $tokenData['access_token'],
            tokenExpiresAt: $expiresAt,
            pageId: $info['page_id'] ?? null,
            instagramAccountId: $info['instagram_account_id'] ?? null,
        );

        $saved = $this->repository->save($account);

        return new ConnectAccountOutput(
            accountId: $saved->id(),
            accountName: $saved->accountName(),
            platform: $saved->platform()->value(),
        );
    }
}
