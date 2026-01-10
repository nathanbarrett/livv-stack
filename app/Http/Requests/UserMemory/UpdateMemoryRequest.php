<?php

declare(strict_types=1);

namespace App\Http\Requests\UserMemory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoryRequest extends FormRequest
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
            'value' => ['required', 'string', 'max:10000'],
        ];
    }
}
