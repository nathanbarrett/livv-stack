<?php

declare(strict_types=1);

namespace App\Http\Requests\Realtime;

use Illuminate\Foundation\Http\FormRequest;

class CreateTokenRequest extends FormRequest
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
            'mode' => ['required', 'string', 'in:general,kanban'],
        ];
    }
}
