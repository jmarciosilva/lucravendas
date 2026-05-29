<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\Entities;

use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Carbon\Carbon;

final class SocialAccount
{
    private function __construct(
        private readonly ?int     $id,
        private readonly string   $tenantId,
        private readonly int      $userId,
        private readonly SocialPlatform $platform,
        private readonly string   $accountId,
        private readonly string   $accountName,
        private readonly string   $accessToken,
        private readonly ?Carbon  $tokenExpiresAt,
        private readonly ?string  $pageId,
        private readonly ?string  $instagramAccountId,
        private bool              $isActive,
    ) {}

    public static function create(
        string        $tenantId,
        int           $userId,
        SocialPlatform $platform,
        string        $accountId,
        string        $accountName,
        string        $accessToken,
        ?Carbon       $tokenExpiresAt = null,
        ?string       $pageId = null,
        ?string       $instagramAccountId = null,
    ): self {
        return new self(
            id: null,
            tenantId: $tenantId,
            userId: $userId,
            platform: $platform,
            accountId: $accountId,
            accountName: $accountName,
            accessToken: $accessToken,
            tokenExpiresAt: $tokenExpiresAt,
            pageId: $pageId,
            instagramAccountId: $instagramAccountId,
            isActive: true,
        );
    }

    public static function restore(
        int           $id,
        string        $tenantId,
        int           $userId,
        SocialPlatform $platform,
        string        $accountId,
        string        $accountName,
        string        $accessToken,
        ?Carbon       $tokenExpiresAt,
        ?string       $pageId,
        ?string       $instagramAccountId,
        bool          $isActive,
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            userId: $userId,
            platform: $platform,
            accountId: $accountId,
            accountName: $accountName,
            accessToken: $accessToken,
            tokenExpiresAt: $tokenExpiresAt,
            pageId: $pageId,
            instagramAccountId: $instagramAccountId,
            isActive: $isActive,
        );
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function id(): ?int                    { return $this->id; }
    public function tenantId(): string            { return $this->tenantId; }
    public function userId(): int                 { return $this->userId; }
    public function platform(): SocialPlatform    { return $this->platform; }
    public function accountId(): string           { return $this->accountId; }
    public function accountName(): string         { return $this->accountName; }
    public function accessToken(): string         { return $this->accessToken; }
    public function tokenExpiresAt(): ?Carbon     { return $this->tokenExpiresAt; }
    public function pageId(): ?string             { return $this->pageId; }
    public function instagramAccountId(): ?string { return $this->instagramAccountId; }
    public function isActive(): bool              { return $this->isActive; }
}
