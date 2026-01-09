<?php

declare(strict_types=1);

namespace App\Http\Requests\Kanban;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskRequest extends FormRequest
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
            'kanban_column_id' => ['required', 'integer', 'exists:kanban_columns,id'],
            'position' => ['required', 'integer', 'min:0'],
        ];
    }
}
