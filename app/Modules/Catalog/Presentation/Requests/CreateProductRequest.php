<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Valida os dados de entrada para criação de produto.
 *
 * Preços são recebidos em reais (float) via JSON e convertidos
 * para centavos (inteiro) antes de chegar ao Use Case.
 */
class CreateProductRequest extends FormRequest
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
            'slug'          => ['nullable', 'string', 'max:220', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'price'         => ['required', 'numeric', 'min:0.01'],
            'compare_price' => ['nullable', 'numeric', 'min:0.01', 'gt:price'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'stock'         => ['nullable', 'integer', 'min:0'],
            'category_id'   => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'        => 'O nome do produto é obrigatório.',
            'price.required'       => 'O preço do produto é obrigatório.',
            'price.min'            => 'O preço deve ser maior que zero.',
            'compare_price.gt'     => 'O preço "de" deve ser maior que o preço atual.',
            'slug.regex'           => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'category_id.exists'   => 'A categoria informada não existe.',
        ];
    }

    /**
     * Prepara os dados ANTES da validação.
     * Gera slug e converte preços de reais para centavos para que
     * os dados validados já estejam no formato correto para os Use Cases.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        // Gera slug automático se não enviado
        if (empty($this->slug) && ! empty($this->name)) {
            $data['slug'] = Str::slug($this->name);
        }

        // Converte preços de reais (float) para centavos (inteiro) antes da validação
        // Isso mantém a regra `gt:price` funcionando corretamente com centavos
        if (! empty($this->price)) {
            $data['price'] = (int) round((float) $this->price * 100);
        }

        if (! empty($this->compare_price)) {
            $data['compare_price'] = (int) round((float) $this->compare_price * 100);
        }

        $this->merge($data);
    }
}
