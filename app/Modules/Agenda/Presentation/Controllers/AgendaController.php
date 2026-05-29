<?php

declare(strict_types=1);

namespace App\Modules\Agenda\Presentation\Controllers;

use App\Modules\Agenda\Application\UseCases\GetAgendaItem\GetAgendaItemHandler;
use App\Modules\Agenda\Application\UseCases\ListAgendaItems\ListAgendaItemsHandler;
use App\Modules\Agenda\Application\UseCases\RegisterForEvent\RegisterForEventCommand;
use App\Modules\Agenda\Application\UseCases\RegisterForEvent\RegisterForEventHandler;
use App\Modules\Agenda\Presentation\Resources\AgendaItemResource;
use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Controller da API de Agenda (eventos, cursos, workshops).
 *
 * Rotas públicas: listagem e detalhe (sem autenticação).
 * Rota protegida: inscrição (requer Sanctum).
 * Tenant identificado pelo header X-Tenant-ID.
 */
final class AgendaController extends Controller
{
    public function __construct(
        private readonly ListAgendaItemsHandler $listHandler,
        private readonly GetAgendaItemHandler $getHandler,
        private readonly RegisterForEventHandler $registerHandler,
    ) {}

    /**
     * GET /api/v1/agenda
     * Lista itens de agenda publicados e futuros do tenant.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');

            // Verifica se eventos de sellers devem aparecer conforme feature flag
            $tenant = TenantModel::find($tenantId);
            $sellerEventsOnMarketplace = $tenant
                ? $tenant->feature('seller_events_on_marketplace')
                : false;

            $itens = $this->listHandler->handle(
                tenantId: $tenantId,
                sellerEventsOnMarketplace: $sellerEventsOnMarketplace,
                type: $request->query('type'),
                fromDate: $request->query('from'),
                apenasGratuitos: (bool) $request->query('gratuito', false),
            );

            return response()->json([
                'data' => AgendaItemResource::collection($itens),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao listar agenda.'], 500);
        }
    }

    /**
     * GET /api/v1/agenda/{slug}
     * Retorna o detalhe de um item de agenda publicado.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID', '');
            $item     = $this->getHandler->handle($tenantId, $slug);

            return response()->json(['data' => new AgendaItemResource($item)]);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Item de agenda não encontrado.'], 404);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao buscar item de agenda.'], 500);
        }
    }

    /**
     * POST /api/v1/agenda/{id}/register
     * Inscreve o usuário autenticado no item de agenda.
     */
    public function register(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);

            $tenantId = $request->header('X-Tenant-ID', '');

            $registration = $this->registerHandler->handle(new RegisterForEventCommand(
                tenantId:      $tenantId,
                agendaItemId:  $id,
                userId:        $request->user()->id,
                notes:         $request->input('notes'),
            ));

            return response()->json([
                'message' => 'Inscrição realizada com sucesso.',
                'data'    => [
                    'id'           => $registration->id,
                    'status'       => $registration->status,
                    'confirmed_at' => $registration->confirmed_at?->toIso8601String(),
                ],
            ], 201);
        } catch (\RuntimeException $e) {
            // Erros de negócio: vagas esgotadas, já inscrito, evento indisponível
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erro ao realizar inscrição.'], 500);
        }
    }
}
