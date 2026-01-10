<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTaskNote;

class UpdateTaskNoteAction
{
    public function handle(KanbanTaskNote $note, string $content): KanbanTaskNote
    {
        $note->update(['note' => $content]);

        return $note->fresh();
    }
}
