<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Enums\KanbanTaskNoteAuthor;
use App\Models\KanbanTask;
use App\Models\KanbanTaskNote;

class CreateTaskNoteAction
{
    public function handle(
        KanbanTask $task,
        string $note,
        KanbanTaskNoteAuthor $author,
    ): KanbanTaskNote {
        return $task->notes()->create([
            'note' => $note,
            'author' => $author->value,
        ]);
    }
}
