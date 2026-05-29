<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Application\UseCases\ConnectAccount\ConnectAccountCommand;
use App\Modules\Marketing\Application\UseCases\ConnectAccount\ConnectAccountHandler;
use App\Modules\Marketing\Application\UseCases\GetOAuthUrl\GetOAuthUrlCommand;
use App\Modules\Marketing\Application\UseCases\GetOAuthUrl\GetOAuthUrlHandler;
use App\Modules\Marketing\Application\UseCases\SchedulePost\SchedulePostCommand;
use App\Modules\Marketing\Application\UseCases\SchedulePost\SchedulePostHandler;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Presentation\Requests\SchedulePostRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class MarketingController extends Controller
{
    public function __construct(
        private readonly GetOAuthUrlHandler               $oauthUrlHandler,
        private readonly ConnectAccountHandler            $connectHandler,
        private readonly SchedulePostHandler              $scheduleHandler,
        private readonly SocialAccountRepositoryInterface $accountRepository,
        private readonly ScheduledPostRepositoryInterface $postRepository,
    ) {}

    /**
     * Retorna a URL de autorização OAuth para o lojista conectar a conta.
     * GET /api/v1/marketing/connect/{platform}
     */
    public function oauthUrl(string $platform, Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $url      = $this->oauthUrlHandler->handle(new GetOAuthUrlCommand($platform, $tenantId));

            return response()->json(['oauth_url' => $url]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao gerar URL de autorização.'], 500);
        }
    }

    /**
     * Callback OAuth — recebe o código do Facebook e persiste a conta social.
     * GET /api/v1/marketing/oauth/callback?code=&state=
     */
    public function oauthCallback(Request $request): JsonResponse
    {
        try {
            $code  = $request->query('code');
            $state = $request->query('state', '');

            if (! $code) {
                return response()->json(['message' => 'Código de autorização ausente.'], 422);
            }

            $stateData = json_decode(base64_decode($state), true);
            $tenantId  = $stateData['tenant_id'] ?? '';
            $platform  = $stateData['platform'] ?? '';

            if (! $tenantId || ! $platform) {
                return response()->json(['message' => 'State inválido.'], 422);
            }

            $userId = $request->user()?->id ?? 0;

            $output = $this->connectHandler->handle(new ConnectAccountCommand(
                tenantId: $tenantId,
                userId: $userId,
                platform: $platform,
                code: $code,
            ));

            return response()->json($output, 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao conectar conta social.'], 500);
        }
    }

    /**
     * Lista contas sociais conectadas do tenant.
     * GET /api/v1/marketing/accounts
     */
    public function accounts(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $accounts = $this->accountRepository->findAllByTenant($tenantId);

            $data = array_map(fn ($a) => [
                'id'           => $a->id(),
                'platform'     => $a->platform()->value(),
                'account_name' => $a->accountName(),
                'is_active'    => $a->isActive(),
                'expires_at'   => $a->tokenExpiresAt()?->toISOString(),
            ], $accounts);

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar contas.'], 500);
        }
    }

    /**
     * Agenda um post manualmente.
     * POST /api/v1/marketing/posts
     */
    public function schedulePost(SchedulePostRequest $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');

            $output = $this->scheduleHandler->handle(new SchedulePostCommand(
                tenantId: $tenantId,
                socialAccountId: (int) $request->validated('social_account_id'),
                caption: $request->validated('caption'),
                imageUrl: $request->validated('image_url'),
                platform: $request->validated('platform'),
                publishAt: Carbon::parse($request->validated('publish_at')),
                productId: $request->validated('product_id') ? (int) $request->validated('product_id') : null,
            ));

            return response()->json($output, 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao agendar post.'], 500);
        }
    }

    /**
     * Lista posts agendados do tenant.
     * GET /api/v1/marketing/posts
     */
    public function listPosts(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $posts    = $this->postRepository->findAllByTenant($tenantId);

            $data = array_map(fn ($p) => [
                'id'               => $p->id(),
                'platform'         => $p->platform()->value(),
                'caption'          => mb_substr($p->caption(), 0, 100) . (mb_strlen($p->caption()) > 100 ? '...' : ''),
                'image_url'        => $p->imageUrl(),
                'status'           => $p->status()->value(),
                'publish_at'       => $p->publishAt()->toISOString(),
                'published_at'     => $p->publishedAt()?->toISOString(),
                'external_post_id' => $p->externalPostId(),
                'error_message'    => $p->errorMessage(),
            ], $posts);

            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar posts.'], 500);
        }
    }
}
