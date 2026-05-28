<?php

declare(strict_types=1);

namespace App\Modules\Shipping\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zipcode' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            // UF necessária para o calculador interno; opcional quando ME está configurado
            'state'   => ['required', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'zipcode.regex' => 'CEP inválido. Use o formato 00000-000.',
            'state.size'    => 'Estado deve ser a sigla UF com 2 letras (ex: SP).',
        ];
    }
}
