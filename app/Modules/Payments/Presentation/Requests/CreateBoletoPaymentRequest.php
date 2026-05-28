<?php

declare(strict_types=1);

namespace App\Modules\Payments\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBoletoPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id'    => ['required', 'integer', 'min:1'],
            'payer_email' => ['required', 'email'],
            'payer_cpf'   => ['required', 'string', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'payer_cpf.required' => 'O CPF do pagador é obrigatório para emissão de boleto.',
            'payer_cpf.regex'    => 'CPF inválido.',
        ];
    }
}
