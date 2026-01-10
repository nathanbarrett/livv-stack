<?php

declare(strict_types=1);

namespace App\Http\Requests\Kanban;

use App\Enums\KanbanTaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'implementation_plans' => ['nullable', 'string', 'max:16777215'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['nullable', Rule::enum(KanbanTaskPriority::class)],
            'dependency_ids' => ['nullable', 'array'],
            'dependency_ids.*' => ['integer', 'exists:kanban_tasks,id'],
        ];
    }
}
