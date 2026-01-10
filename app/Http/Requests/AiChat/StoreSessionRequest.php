<?php

declare(strict_types=1);

namespace App\Http\Requests\AiChat;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:100'],
            'settings' => ['nullable', 'array'],
            'settings.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'settings.max_tokens' => ['nullable', 'integer', 'min:1', 'max:128000'],
            'settings.system_prompt' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
