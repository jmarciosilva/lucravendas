<?php

declare(strict_types=1);

use App\Jobs\PublishScheduledPost;
use App\Modules\Catalog\Domain\Events\ProductCreated;
use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Marketing\Application\UseCases\AutoScheduleProductPost\AutoScheduleProductPostHandler;
use App\Modules\Marketing\Domain\Contracts\SocialGatewayInterface;
use App\Modules\Marketing\Domain\ValueObjects\PostStatus;
use App\Modules\Marketing\Infrastructure\Models\ScheduledPostModel;
use App\Modules\Marketing\Infrastructure\Models\SocialAccountModel;
use Illuminate\Support\Facades\Event;

// Helper: cria uma conta social conectada para um tenant
function criarContaSocial(string $tenantId, int $userId, string $platform = 'instagram'): SocialAccountModel
{
    return SocialAccountModel::create([
        'tenant_id'             => $tenantId,
        'user_id'               => $userId,
        'platform'              => $platform,
        'account_id'            => 'acc_' . uniqid(),
        'account_name'          => 'Loja Test',
        'access_token'          => 'fake-token-' . uniqid(),
        'instagram_account_id'  => $platform === 'instagram' ? 'ig_' . uniqid() : null,
        'page_id'               => $platform === 'facebook' ? 'pg_' . uniqid() : null,
        'is_active'             => true,
    ]);
}

it('agenda post manualmente e retorna 201 com dados corretos', function () {
    $tenantId = 'tenant-mkt-1';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $this->actingAs($user)
        ->postJson('/api/v1/marketing/posts', [
            'social_account_id' => $conta->id,
            'caption'           => 'Confira nosso novo produto!',
            'image_url'         => 'https://example.com/img.jpg',
            'platform'          => 'instagram',
            'publish_at'        => now()->addHour()->toISOString(),
        ], ['X-Tenant-ID' => $tenantId])
        ->assertCreated()
        ->assertJsonPath('status', PostStatus::PENDING)
        ->assertJsonPath('platform', 'instagram')
        ->assertJsonStructure(['post_id', 'status', 'publish_at', 'platform']);
});

it('retorna 401 ao agendar post sem autenticação', function () {
    $this->postJson('/api/v1/marketing/posts', [
        'social_account_id' => 1,
        'caption'           => 'Teste',
        'image_url'         => 'https://example.com/img.jpg',
        'platform'          => 'instagram',
        'publish_at'        => now()->addHour()->toISOString(),
    ], ['X-Tenant-ID' => 'any'])
        ->assertUnauthorized();
});

it('retorna 422 quando publish_at está no passado', function () {
    $tenantId = 'tenant-mkt-2';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $this->actingAs($user)
        ->postJson('/api/v1/marketing/posts', [
            'social_account_id' => $conta->id,
            'caption'           => 'Post no passado',
            'image_url'         => 'https://example.com/img.jpg',
            'platform'          => 'instagram',
            'publish_at'        => now()->subHour()->toISOString(),
        ], ['X-Tenant-ID' => $tenantId])
        ->assertUnprocessable();
});

it('retorna 422 quando conta pertence a outro tenant', function () {
    $tenantId      = 'tenant-mkt-3';
    $outroTenantId = 'outro-tenant';
    $user          = loginComo('tenant_admin');
    $contaAjena    = criarContaSocial($outroTenantId, $user->id);

    $this->actingAs($user)
        ->postJson('/api/v1/marketing/posts', [
            'social_account_id' => $contaAjena->id,
            'caption'           => 'Tentativa de usar conta alheia',
            'image_url'         => 'https://example.com/img.jpg',
            'platform'          => 'instagram',
            'publish_at'        => now()->addHour()->toISOString(),
        ], ['X-Tenant-ID' => $tenantId])
        ->assertUnprocessable();
});

it('lista apenas posts do tenant correto', function () {
    $tenantId      = 'tenant-mkt-4';
    $outroTenantId = 'outro-tenant-4';
    $user          = loginComo('tenant_admin');
    $conta1        = criarContaSocial($tenantId, $user->id);
    $conta2        = criarContaSocial($outroTenantId, $user->id);

    // Cria post para o tenant correto
    ScheduledPostModel::create([
        'tenant_id'        => $tenantId,
        'social_account_id' => $conta1->id,
        'caption'          => 'Post do tenant correto',
        'image_url'        => 'https://example.com/img.jpg',
        'platform'         => 'instagram',
        'status'           => PostStatus::PENDING,
        'publish_at'       => now()->addHour(),
    ]);

    // Cria post para outro tenant
    ScheduledPostModel::create([
        'tenant_id'        => $outroTenantId,
        'social_account_id' => $conta2->id,
        'caption'          => 'Post de outro tenant',
        'image_url'        => 'https://example.com/img2.jpg',
        'platform'         => 'facebook',
        'status'           => PostStatus::PENDING,
        'publish_at'       => now()->addHour(),
    ]);

    $this->actingAs($user)
        ->getJson('/api/v1/marketing/posts', ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.platform', 'instagram');
});

it('job publica post pendente com publish_at vencido', function () {
    $tenantId = 'tenant-mkt-5';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $post = ScheduledPostModel::create([
        'tenant_id'        => $tenantId,
        'social_account_id' => $conta->id,
        'caption'          => 'Post vencido',
        'image_url'        => 'https://example.com/img.jpg',
        'platform'         => 'instagram',
        'status'           => PostStatus::PENDING,
        'publish_at'       => now()->subMinutes(10), // vencido
    ]);

    // Mock do gateway para simular publicação bem-sucedida
    $this->mock(SocialGatewayInterface::class)
        ->shouldReceive('publishPost')
        ->once()
        ->andReturn('external_post_123');

    // Dispara o job diretamente
    app(PublishScheduledPost::class)->handle(
        app(\App\Modules\Marketing\Application\UseCases\PublishPost\PublishPostHandler::class),
        app(\App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface::class),
    );

    $this->assertDatabaseHas('scheduled_posts', [
        'id'               => $post->id,
        'status'           => PostStatus::PUBLISHED,
        'external_post_id' => 'external_post_123',
    ]);
});

it('job marca post como failed quando gateway lança exceção', function () {
    $tenantId = 'tenant-mkt-6';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $post = ScheduledPostModel::create([
        'tenant_id'        => $tenantId,
        'social_account_id' => $conta->id,
        'caption'          => 'Post que falha',
        'image_url'        => 'https://example.com/img.jpg',
        'platform'         => 'instagram',
        'status'           => PostStatus::PENDING,
        'publish_at'       => now()->subMinutes(5),
    ]);

    $this->mock(SocialGatewayInterface::class)
        ->shouldReceive('publishPost')
        ->once()
        ->andThrow(new \RuntimeException('API do Instagram indisponível'));

    app(PublishScheduledPost::class)->handle(
        app(\App\Modules\Marketing\Application\UseCases\PublishPost\PublishPostHandler::class),
        app(\App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface::class),
    );

    $this->assertDatabaseHas('scheduled_posts', [
        'id'     => $post->id,
        'status' => PostStatus::FAILED,
    ]);

    $this->assertDatabaseMissing('scheduled_posts', [
        'id'     => $post->id,
        'error_message' => null,
    ]);
});

it('listener ProductCreated não cria post quando MARKETING_AUTO_POST está habilitado mas produto sem imagem', function () {
    // Handler pula produtos sem imagem — nenhum post deve ser criado
    config(['marketing.auto_post_on_product_created' => true]);

    $tenantId = 'tenant-mkt-7';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $productModel = ProductModel::create([
        'name'      => 'Produto Marketing',
        'slug'      => 'produto-mkt-' . uniqid(),
        'price'     => 5000,
        'stock'     => 10,
        'status'    => 'active',
        'tenant_id' => $tenantId,
    ]);

    $productRepo = app(\App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface::class);
    $product     = $productRepo->findById($productModel->id, $tenantId);

    // Dispara evento — handler deve rodar sem exceção mas não criar posts (sem imagem)
    event(new ProductCreated($product));

    // Sem imagem no produto → handler sai cedo → zero posts agendados
    expect(ScheduledPostModel::where('tenant_id', $tenantId)->count())->toBe(0);
});

it('listener ProductCreated não cria post quando MARKETING_AUTO_POST está desabilitado', function () {
    config(['marketing.auto_post_on_product_created' => false]);

    $tenantId = 'tenant-mkt-8';
    $user     = loginComo('tenant_admin');
    $conta    = criarContaSocial($tenantId, $user->id);

    $productModel = ProductModel::create([
        'name'      => 'Produto Sem Post',
        'slug'      => 'produto-sem-post-' . uniqid(),
        'price'     => 3000,
        'stock'     => 5,
        'status'    => 'active',
        'tenant_id' => $tenantId,
    ]);

    $productRepo = app(\App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface::class);
    $product     = $productRepo->findById($productModel->id, $tenantId);

    event(new ProductCreated($product));

    // Config desabilitado → nenhum post deve ser criado
    expect(ScheduledPostModel::where('tenant_id', $tenantId)->count())->toBe(0);
});

it('retorna OAuth URL para plataforma válida', function () {
    $tenantId = 'tenant-mkt-9';
    $user     = loginComo('tenant_admin');

    $this->mock(SocialGatewayInterface::class)
        ->shouldReceive('getOAuthUrl')
        ->once()
        ->andReturn('https://facebook.com/dialog/oauth?client_id=123');

    $this->actingAs($user)
        ->getJson('/api/v1/marketing/connect/instagram', ['X-Tenant-ID' => $tenantId])
        ->assertOk()
        ->assertJsonStructure(['oauth_url']);
});

it('retorna 422 para plataforma inválida no OAuth URL', function () {
    $tenantId = 'tenant-mkt-10';
    $user     = loginComo('tenant_admin');

    $this->actingAs($user)
        ->getJson('/api/v1/marketing/connect/tiktok', ['X-Tenant-ID' => $tenantId])
        ->assertUnprocessable();
});
