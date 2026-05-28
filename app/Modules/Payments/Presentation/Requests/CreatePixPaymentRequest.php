<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePixPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return ['order_id.required' => 'O ID do pedido é obrigatório.'];
    }
}
