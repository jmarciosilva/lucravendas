<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'min:1'],
            'variant_id' => ['nullable', 'integer', 'min:1'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'quantity.required'   => 'A quantidade é obrigatória.',
            'quantity.min'        => 'A quantidade mínima é 1.',
            'quantity.max'        => 'A quantidade máxima por item é 999.',
        ];
    }
}
