<?php

declare(strict_types=1);

namespace App\Modules\Orders\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'payment_method'     => ['nullable', 'string', 'in:pix,credit_card,boleto'],
            'notes'              => ['nullable', 'string', 'max:500'],

            // Opção de frete selecionada (resultado do /shipping/calculate)
            'shipping_option_id' => ['nullable', 'string'],

            // Endereço de entrega — obrigatório quando há opção de frete selecionada
            'recipient_name'       => ['nullable', 'string', 'max:200'],
            'recipient_zipcode'    => ['nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'recipient_address'    => ['nullable', 'string', 'max:255'],
            'recipient_number'     => ['nullable', 'string', 'max:20'],
            'recipient_complement' => ['nullable', 'string', 'max:100'],
            'recipient_city'       => ['nullable', 'string', 'max:100'],
            'recipient_state'      => ['nullable', 'string', 'size:2'],

            // Código do serviço de frete (ME: "1"=PAC, "2"=SEDEX; ou código personalizado)
            'shipping_service_code' => ['nullable', 'string'],
        ];
    }
}
