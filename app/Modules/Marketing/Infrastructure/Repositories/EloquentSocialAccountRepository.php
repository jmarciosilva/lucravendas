<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Infrastructure\Repositories;

use App\Modules\Marketing\Domain\Entities\SocialAccount;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use App\Modules\Marketing\Infrastructure\Models\SocialAccountModel;
use Carbon\Carbon;

final class EloquentSocialAccountRepository implements SocialAccountRepositoryInterface
{
    public function save(SocialAccount $account): SocialAccount
    {
        $data = [
            'tenant_id'            => $account->tenantId(),
            'user_id'              => $account->userId(),
            'platform'             => $account->platform()->value(),
            'account_id'           => $account->accountId(),
            'account_name'         => $account->accountName(),
            'access_token'         => $account->accessToken(),
            'token_expires_at'     => $account->tokenExpiresAt(),
            'page_id'              => $account->pageId(),
            'instagram_account_id' => $account->instagramAccountId(),
            'is_active'            => $account->isActive(),
        ];

        if ($account->id() !== null) {
            SocialAccountModel::where('id', $account->id())->update($data);
            $model = SocialAccountModel::find($account->id());
        } else {
            $model = SocialAccountModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function findById(int $id, string $tenantId): ?SocialAccount
    {
        $model = SocialAccountModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByTenant(string $tenantId): array
    {
        return SocialAccountModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SocialAccountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findActiveByTenant(string $tenantId): array
    {
        return SocialAccountModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SocialAccountModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(SocialAccountModel $model): SocialAccount
    {
        return SocialAccount::restore(
            id: $model->id,
            tenantId: $model->tenant_id,
            userId: $model->user_id,
            platform: SocialPlatform::fromString($model->platform),
            accountId: $model->account_id,
            accountName: $model->account_name,
            accessToken: $model->access_token,
            tokenExpiresAt: $model->token_expires_at ? Carbon::parse($model->token_expires_at) : null,
            pageId: $model->page_id,
            instagramAccountId: $model->instagram_account_id,
            isActive: (bool) $model->is_active,
        );
    }
}
