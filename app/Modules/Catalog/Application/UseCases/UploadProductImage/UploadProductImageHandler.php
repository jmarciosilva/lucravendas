<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\UseCases\UploadProductImage;

use App\Modules\Catalog\Infrastructure\Models\ProductModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orquestra o upload e associação de imagem a um produto.
 *
 * Utiliza o spatie/laravel-medialibrary para armazenar a imagem
 * e gerar automaticamente a miniatura (conversão 'thumb').
 */
final class UploadProductImageHandler
{
    public function handle(UploadProductImageCommand $command): array
    {
        return DB::transaction(function () use ($command) {
            $model = ProductModel::query()
                ->where('id', $command->productId)
                ->where('tenant_id', $command->tenantId)
                ->first();

            if ($model === null) {
                throw new RuntimeException('Produto não encontrado.');
            }

            // Adiciona a imagem à coleção 'images' e dispara a geração do thumb automaticamente
            $media = $model
                ->addMedia($command->file)
                ->toMediaCollection('images');

            return [
                'id'        => $media->id,
                'url'       => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'mime_type' => $media->mime_type,
                'size'      => $media->size,
            ];
        });
    }
}
