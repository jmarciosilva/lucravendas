<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Application\UseCases\UploadProductImage\UploadProductImageCommand;
use App\Modules\Catalog\Application\UseCases\UploadProductImage\UploadProductImageHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Gerencia o upload de imagens associadas a um produto.
 * Requer autenticação e role tenant_admin.
 */
final class ProductImageController extends Controller
{
    public function __construct(
        private readonly UploadProductImageHandler $handler,
    ) {}

    /**
     * Recebe e armazena uma imagem para o produto informado.
     *
     * A imagem deve ser enviada como multipart/form-data no campo 'image'.
     * Tipos aceitos: jpeg, png, webp, gif. Tamanho máximo: 5MB.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp,gif', 'max:5120'],
        ], [
            'image.required' => 'O arquivo de imagem é obrigatório.',
            'image.image'    => 'O arquivo deve ser uma imagem.',
            'image.mimes'    => 'Formatos aceitos: JPEG, PNG, WebP, GIF.',
            'image.max'      => 'A imagem deve ter no máximo 5MB.',
        ]);

        try {
            $result = $this->handler->handle(new UploadProductImageCommand(
                productId: $productId,
                tenantId: $request->header('X-Tenant-ID', ''),
                file: $request->file('image'),
            ));

            return response()->json(['data' => $result], 201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao fazer upload da imagem.'], 500);
        }
    }
}
