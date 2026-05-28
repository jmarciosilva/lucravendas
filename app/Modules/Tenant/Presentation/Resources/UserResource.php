<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma o modelo User para o formato de resposta da API.
 *
 * @mixin \App\Models\User
 */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'roles'      => $this->getRoleNames(),
            'tenant_id'  => $this->tenant_id,
            'status'     => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
