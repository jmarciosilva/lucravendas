<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\GetOAuthUrl;

use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;

final class GetOAuthUrlHandler
{
    public function __construct(
        private readonly SocialGatewayInterface $gateway,
    ) {}

    public function handle(GetOAuthUrlCommand $command): string
    {
        // Valida plataforma antes de chamar o gateway
        SocialPlatform::fromString($command->platform);

        // O state codifica o tenantId para recuperá-lo no callback
        $state = base64_encode(json_encode([
            'tenant_id' => $command->tenantId,
            'platform'  => $command->platform,
        ]));

        return $this->gateway->getOAuthUrl($command->platform, $state);
    }
}
