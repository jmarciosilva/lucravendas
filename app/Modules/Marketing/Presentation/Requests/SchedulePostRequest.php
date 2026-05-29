<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Presentation\Requests;

use App\Modules\Marketing\Domain\ValueObjects\SocialPlatform;
use Illuminate\Foundation\Http\FormRequest;

final class SchedulePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'social_account_id' => ['required', 'integer'],
            'caption'           => ['required', 'string', 'max:2200'],
            'image_url'         => ['required', 'url'],
            'platform'          => ['required', 'string', 'in:' . implode(',', SocialPlatform::valid())],
            'publish_at'        => ['required', 'date', 'after:now'],
            'product_id'        => ['nullable', 'integer'],
        ];
    }
}
