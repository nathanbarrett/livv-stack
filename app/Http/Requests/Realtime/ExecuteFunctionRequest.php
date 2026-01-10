<?php

declare(strict_types=1);

namespace App\Http\Requests\Realtime;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteFunctionRequest extends FormRequest
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
            'function_name' => ['required', 'string', 'in:manage_kanban,manage_user_memory'],
            'arguments' => ['required', 'array'],
            'call_id' => ['required', 'string'],
        ];
    }
}
