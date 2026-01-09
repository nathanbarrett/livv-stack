<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTask;

class DeleteTaskAction
{
    public function handle(KanbanTask $task): void
    {
        $task->delete();
    }
}
