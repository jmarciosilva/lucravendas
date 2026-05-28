<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma dados de autenticação para o formato de resposta da API.
 *
 * @mixin \App\Models\User
 */
final class AuthResource extends JsonResource
{
    private string $token;

    private string $tokenType;

    public function withToken(string $token, string $tokenType = 'Bearer'): self
    {
        $this->token = $token;
        $this->tokenType = $tokenType;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id'         => $this->id,
                'name'       => $this->name,
                'email'      => $this->email,
                'roles'      => $this->getRoleNames(),
                'tenant_id'  => $this->tenant_id,
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'token'      => $this->token ?? null,
            'token_type' => $this->tokenType ?? 'Bearer',
        ];
    }
}
