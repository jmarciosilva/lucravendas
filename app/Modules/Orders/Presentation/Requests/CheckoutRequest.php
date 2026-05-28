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
            'payment_method' => ['nullable', 'string', 'in:pix,credit_card,boleto'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }
}
