<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\UseCases\GetCart;

use App\Modules\Orders\Domain\Entities\Cart;
use App\Modules\Orders\Domain\Repositories\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Busca ou cria o carrinho do visitante/usuário.
 * Se ambos session_id e user_id são fornecidos, faz a mesclagem do
 * carrinho anônimo no carrinho autenticado.
 */
final class GetCartHandler
{
    public function __construct(
        private readonly CartRepositoryInterface $repository,
    ) {}

    public function handle(string $tenantId, ?string $sessionId, ?int $userId): Cart
    {
        // Usuário autenticado com session_id → tenta mesclar carrinho anônimo
        if ($userId !== null && $sessionId !== null) {
            return DB::transaction(function () use ($tenantId, $sessionId, $userId) {
                return $this->mergeAndGetCart($tenantId, $sessionId, $userId);
            });
        }

        if ($userId !== null) {
            $cart = $this->repository->findByUserId($userId, $tenantId);

            if ($cart === null) {
                $cart = Cart::create($tenantId, null, $userId);
                $cart = $this->repository->save($cart);
            }

            return $cart;
        }

        // Visitante anônimo: usa session_id
        $cart = $this->repository->findBySessionId($sessionId ?? '', $tenantId);

        if ($cart === null) {
            $cart = Cart::create($tenantId, $sessionId, null);
            $cart = $this->repository->save($cart);
        }

        return $cart;
    }

    private function mergeAndGetCart(string $tenantId, string $sessionId, int $userId): Cart
    {
        $userCart    = $this->repository->findByUserId($userId, $tenantId);
        $sessionCart = $this->repository->findBySessionId($sessionId, $tenantId);

        if ($userCart === null) {
            $userCart = Cart::create($tenantId, null, $userId);
            $userCart = $this->repository->save($userCart);
        }

        // Mescla itens do carrinho anônimo no carrinho do usuário
        if ($sessionCart !== null && ! $sessionCart->isEmpty()) {
            foreach ($sessionCart->items() as $item) {
                $userCart->addItem($item);
            }
            $this->repository->save($userCart);
            $this->repository->delete($sessionCart->id());

            // Recarrega para obter IDs corretos dos itens
            $userCart = $this->repository->findByUserId($userId, $tenantId);
        }

        return $userCart;
    }
}
