<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Domain\Contracts;

use App\Modules\Marketing\Domain\Entities\SocialAccount;

interface SocialGatewayInterface
{
    /**
     * Retorna a URL de autorização OAuth para o usuário acessar no browser.
     *
     * @param  string $platform  'instagram' | 'facebook'
     * @param  string $state     Token de estado (contém tenantId codificado)
     */
    public function getOAuthUrl(string $platform, string $state): string;

    /**
     * Troca o código de autorização por um access token de longa duração (~60 dias).
     *
     * @return array{access_token: string, expires_in: int}
     */
    public function exchangeCodeForToken(string $code): array;

    /**
     * Busca informações da conta vinculada ao token.
     *
     * @return array{account_id: string, account_name: string, page_id: string|null, instagram_account_id: string|null}
     */
    public function getAccountInfo(string $accessToken, string $platform): array;

    /**
     * Publica um post com imagem na conta social.
     *
     * @return string ID externo do post publicado
     */
    public function publishPost(SocialAccount $account, string $caption, string $imageUrl): string;
}
