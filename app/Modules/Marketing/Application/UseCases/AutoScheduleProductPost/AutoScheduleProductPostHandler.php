<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Application\UseCases\AutoScheduleProductPost;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use App\Modules\Marketing\Domain\Entities\ScheduledPost;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use App\Modules\Marketing\Domain\Repositories\SocialAccountRepositoryInterface;
use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class AutoScheduleProductPostHandler
{
    public function __construct(
        private readonly SocialAccountRepositoryInterface  $accountRepository,
        private readonly ScheduledPostRepositoryInterface  $postRepository,
    ) {}

    public function handle(AutoScheduleProductPostCommand $command): void
    {
        $product = $command->product;

        // Busca a primeira imagem do produto via Eloquent (medialibrary)
        $productModel = ProductModel::find($product->id());
        $imageUrl     = $productModel?->getFirstMediaUrl('images');

        if (! $imageUrl) {
            // Produto sem imagem — não é possível criar post com mídia
            Log::debug("AutoScheduleProductPost: produto #{$product->id()} sem imagem, post não agendado.");
            return;
        }

        $activeAccounts = $this->accountRepository->findActiveByTenant($command->tenantId);

        if (empty($activeAccounts)) {
            return; // Tenant sem contas conectadas
        }

        $caption = $this->buildCaption($product->name(), $product->description());

        foreach ($activeAccounts as $account) {
            $post = ScheduledPost::create(
                tenantId: $command->tenantId,
                socialAccountId: $account->id(),
                caption: $caption,
                imageUrl: $imageUrl,
                platform: SocialPlatform::fromString($account->platform()->value()),
                publishAt: Carbon::now(),
                productId: $product->id(),
            );

            $this->postRepository->save($post);
        }
    }

    private function buildCaption(string $name, ?string $description): string
    {
        $caption = $name;

        if ($description) {
            // Limita a descrição a 300 caracteres para caber na legenda
            $excerpt = mb_strlen($description) > 300
                ? mb_substr($description, 0, 297) . '...'
                : $description;

            $caption .= "\n\n" . $excerpt;
        }

        return $caption;
    }
}
