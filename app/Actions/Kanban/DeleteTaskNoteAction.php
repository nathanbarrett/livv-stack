<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTaskNote;

class DeleteTaskNoteAction
{
    public function handle(KanbanTaskNote $note): void
    {
        $note->delete();
    }
}
