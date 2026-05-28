<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'bank_info'   => ['nullable', 'array'],
        ];
    }

    /** Gera slug automaticamente a partir do nome se não informado */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && ! empty($this->name)) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }
}
