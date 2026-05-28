<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida os dados de entrada para atualização de produto.
 * Slug não é atualizável após criação para não quebrar URLs existentes.
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|object>> */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:200'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'price'         => ['required', 'numeric', 'min:0.01'],
            'compare_price' => ['nullable', 'numeric', 'min:0.01', 'gt:price'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'category_id'   => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'    => 'O nome do produto é obrigatório.',
            'price.required'   => 'O preço do produto é obrigatório.',
            'price.min'        => 'O preço deve ser maior que zero.',
            'compare_price.gt' => 'O preço "de" deve ser maior que o preço atual.',
        ];
    }

    /**
     * Converte preços de reais para centavos ANTES da validação,
     * garantindo que os dados validados já estejam no formato correto.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if (! empty($this->price)) {
            $data['price'] = (int) round((float) $this->price * 100);
        }

        if (! empty($this->compare_price)) {
            $data['compare_price'] = (int) round((float) $this->compare_price * 100);
        }

        $this->merge($data);
    }
}
