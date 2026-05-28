<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Presentation\Resources\ProductResource;
use App\Modules\Marketplace\Application\UseCases\GetSellerDashboard\GetSellerDashboardHandler;
use App\Modules\Marketplace\Application\UseCases\GetSellerProfile\GetSellerProfileCommand;
use App\Modules\Marketplace\Application\UseCases\GetSellerProfile\GetSellerProfileHandler;
use App\Modules\Marketplace\Application\UseCases\ListSellers\ListSellersHandler;
use App\Modules\Marketplace\Application\UseCases\RegisterSeller\RegisterSellerCommand;
use App\Modules\Marketplace\Application\UseCases\RegisterSeller\RegisterSellerHandler;
use App\Modules\Marketplace\Infrastructure\Models\SellerModel;
use App\Modules\Marketplace\Presentation\Requests\RegisterSellerRequest;
use App\Modules\Marketplace\Presentation\Resources\SellerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use RuntimeException;

final class SellerController extends Controller
{
    public function __construct(
        private readonly ListSellersHandler        $listSellersHandler,
        private readonly GetSellerProfileHandler   $getSellerProfileHandler,
        private readonly RegisterSellerHandler     $registerSellerHandler,
        private readonly GetSellerDashboardHandler $getDashboardHandler,
    ) {}

    /** Lista todos os sellers ativos do tenant */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $sellers  = $this->listSellersHandler->handle($tenantId);

            return SellerResource::collection($sellers);
        } catch (\Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    /** Exibe o perfil público de um seller pelo slug */
    public function show(Request $request, string $slug): SellerResource|JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $seller   = $this->getSellerProfileHandler->handle(
                new GetSellerProfileCommand($slug, $tenantId)
            );

            return new SellerResource($seller);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 404 ? 404 : 422;
            return response()->json(['message' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }

    /** Lista produtos ativos de um seller */
    public function products(Request $request, string $slug): JsonResponse|AnonymousResourceCollection
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');

            $sellerModel = SellerModel::where('slug', $slug)
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->first();

            if ($sellerModel === null) {
                return response()->json(['message' => 'Seller não encontrado.'], 404);
            }

            $products = $sellerModel->products()
                ->where('status', 'active')
                ->with(['media', 'category'])
                ->paginate(20);

            return ProductResource::collection($products);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }

    /** Cadastra um novo seller (usuário autenticado) */
    public function register(RegisterSellerRequest $request): SellerResource|JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $userId   = $request->user()->id;

            $seller = $this->registerSellerHandler->handle(new RegisterSellerCommand(
                tenantId: $tenantId,
                userId: $userId,
                name: $request->validated('name'),
                slug: $request->validated('slug'),
                description: $request->validated('description'),
                bankInfo: $request->validated('bank_info', []),
            ));

            return (new SellerResource($seller))->response()->setStatusCode(201);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }

    /** Retorna o dashboard do seller autenticado */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $userId   = $request->user()->id;

            $data = $this->getDashboardHandler->handle($userId, $tenantId);

            return response()->json(['data' => $data]);
        } catch (RuntimeException $e) {
            $code = $e->getCode() === 404 ? 404 : 422;
            return response()->json(['message' => $e->getMessage()], $code);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }
}
