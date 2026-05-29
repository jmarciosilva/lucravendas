<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Infrastructure\Gateways;

use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\Entities\SocialAccount;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class MetaGraphGateway implements SocialGatewayInterface
{
    private readonly string $appId;
    private readonly string $appSecret;
    private readonly string $redirectUri;
    private readonly string $apiVersion;

    public function __construct()
    {
        $this->appId       = (string) config('marketing.meta.app_id');
        $this->appSecret   = (string) config('marketing.meta.app_secret');
        $this->redirectUri = (string) config('marketing.meta.redirect_uri');
        $this->apiVersion  = (string) config('marketing.meta.api_version', 'v19.0');
    }

    public function getOAuthUrl(string $platform, string $state): string
    {
        $scopes = [
            'pages_manage_posts',
            'pages_read_engagement',
            'instagram_basic',
            'instagram_content_publish',
        ];

        $params = http_build_query([
            'client_id'     => $this->appId,
            'redirect_uri'  => $this->redirectUri,
            'scope'         => implode(',', $scopes),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return "https://www.facebook.com/{$this->apiVersion}/dialog/oauth?{$params}";
    }

    public function exchangeCodeForToken(string $code): array
    {
        // Troca o code por short-lived token
        $response = Http::get('https://graph.facebook.com/oauth/access_token', [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao trocar código OAuth: ' . $response->body());
        }

        $shortToken = $response->json('access_token');

        // Troca por long-lived token (~60 dias)
        $longResponse = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $this->appId,
            'client_secret'     => $this->appSecret,
            'fb_exchange_token' => $shortToken,
        ]);

        if ($longResponse->failed()) {
            throw new RuntimeException('Falha ao trocar por long-lived token: ' . $longResponse->body());
        }

        return [
            'access_token' => $longResponse->json('access_token'),
            'expires_in'   => (int) ($longResponse->json('expires_in') ?? 5183944),
        ];
    }

    public function getAccountInfo(string $accessToken, string $platform): array
    {
        $http = $this->httpWithToken($accessToken);

        // Busca páginas do Facebook vinculadas ao token
        $pagesResponse = $http->get('/me/accounts');

        if ($pagesResponse->failed()) {
            throw new RuntimeException('Falha ao buscar páginas: ' . $pagesResponse->body());
        }

        $pages = $pagesResponse->json('data', []);
        $page  = $pages[0] ?? null;

        if ($platform === SocialPlatform::FACEBOOK) {
            if (! $page) {
                throw new RuntimeException('Nenhuma página do Facebook encontrada para este token.');
            }

            return [
                'account_id'            => $page['id'],
                'account_name'          => $page['name'],
                'page_id'               => $page['id'],
                'instagram_account_id'  => null,
            ];
        }

        // Instagram: busca o Instagram Business Account vinculado à página
        if (! $page) {
            throw new RuntimeException('Nenhuma página do Facebook encontrada (necessária para conectar Instagram).');
        }

        $igResponse = $http->get("/{$page['id']}", [
            'fields' => 'instagram_business_account',
        ]);

        if ($igResponse->failed()) {
            throw new RuntimeException('Falha ao buscar conta do Instagram: ' . $igResponse->body());
        }

        $igAccountId = $igResponse->json('instagram_business_account.id');

        if (! $igAccountId) {
            throw new RuntimeException('Página não possui conta do Instagram Business vinculada.');
        }

        // Busca nome da conta Instagram
        $igNameResponse = $http->get("/{$igAccountId}", ['fields' => 'name,username']);
        $igName         = $igNameResponse->json('username') ?? $igNameResponse->json('name') ?? 'Instagram';

        return [
            'account_id'            => $igAccountId,
            'account_name'          => $igName,
            'page_id'               => $page['id'],
            'instagram_account_id'  => $igAccountId,
        ];
    }

    public function publishPost(SocialAccount $account, string $caption, string $imageUrl): string
    {
        if ($account->platform()->isInstagram()) {
            return $this->publishInstagram($account, $caption, $imageUrl);
        }

        return $this->publishFacebook($account, $caption, $imageUrl);
    }

    // -----------------------------------------------------------------------
    // Métodos privados
    // -----------------------------------------------------------------------

    private function publishInstagram(SocialAccount $account, string $caption, string $imageUrl): string
    {
        $igAccountId = $account->instagramAccountId();

        if (! $igAccountId) {
            throw new RuntimeException('Conta do Instagram não possui instagram_account_id configurado.');
        }

        $http = $this->httpWithToken($account->accessToken());

        // Passo 1: cria container de mídia
        $containerResponse = $http->post("/{$igAccountId}/media", [
            'image_url' => $imageUrl,
            'caption'   => $caption,
        ]);

        if ($containerResponse->failed()) {
            throw new RuntimeException('Falha ao criar container de mídia no Instagram: ' . $containerResponse->body());
        }

        $creationId = $containerResponse->json('id');

        if (! $creationId) {
            throw new RuntimeException('Instagram não retornou creation_id para o container de mídia.');
        }

        // Passo 2: publica o container
        $publishResponse = $http->post("/{$igAccountId}/media_publish", [
            'creation_id' => $creationId,
        ]);

        if ($publishResponse->failed()) {
            throw new RuntimeException('Falha ao publicar post no Instagram: ' . $publishResponse->body());
        }

        return (string) $publishResponse->json('id');
    }

    private function publishFacebook(SocialAccount $account, string $caption, string $imageUrl): string
    {
        $pageId = $account->pageId();

        if (! $pageId) {
            throw new RuntimeException('Conta do Facebook não possui page_id configurado.');
        }

        $http = $this->httpWithToken($account->accessToken());

        $response = $http->post("/{$pageId}/photos", [
            'url'     => $imageUrl,
            'message' => $caption,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Falha ao publicar foto no Facebook: ' . $response->body());
        }

        return (string) ($response->json('post_id') ?? $response->json('id'));
    }

    private function httpWithToken(string $token): PendingRequest
    {
        return Http::withToken($token)
            ->baseUrl("https://graph.facebook.com/{$this->apiVersion}");
    }
}
