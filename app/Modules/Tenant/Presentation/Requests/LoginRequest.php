<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validação dos dados de login.
 */
final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email'       => ['required', 'email:rfc'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required'    => 'O e-mail é obrigatório.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }
}
