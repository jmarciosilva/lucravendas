<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Valida e normaliza os dados de entrada para criação de categoria.
 * O slug é gerado automaticamente a partir do nome se não for informado.
 */
class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A autorização de role (tenant_admin) é feita no middleware da rota
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'slug'       => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'parent_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required'  => 'O nome da categoria é obrigatório.',
            'slug.regex'     => 'O slug deve conter apenas letras minúsculas, números e hífens.',
            'parent_id.exists' => 'A categoria pai informada não existe.',
        ];
    }

    /**
     * Normaliza os dados ANTES da validação para que o slug gerado
     * automaticamente seja incluído nos dados validados pelo Laravel.
     * passedValidation() não funciona aqui pois a validação já ocorreu.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && ! empty($this->name)) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }
}
