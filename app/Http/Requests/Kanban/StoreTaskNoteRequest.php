<?php

declare(strict_types=1);

namespace App\Http\Requests\Kanban;

use App\Enums\KanbanTaskNoteAuthor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskNoteRequest extends FormRequest
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
            'note' => ['required', 'string', 'max:65535'],
            'author' => ['required', Rule::enum(KanbanTaskNoteAuthor::class)],
        ];
    }
}
