<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCardPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id'     => ['required', 'integer', 'min:1'],
            'card_token'   => ['required', 'string'],
            'installments' => ['required', 'integer', 'min:1', 'max:12'],
            'payer_email'  => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_token.required'   => 'O token do cartão é obrigatório.',
            'installments.required' => 'O número de parcelas é obrigatório.',
            'payer_email.required'  => 'O e-mail do pagador é obrigatório.',
        ];
    }
}
