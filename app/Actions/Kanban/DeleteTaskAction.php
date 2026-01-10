<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanTask;

class DeleteTaskAction
{
    public function handle(KanbanTask $task): void
    {
        $board = $task->column->board;
        $taskId = $task->id;

        $task->delete();

        KanbanBoardUpdated::dispatch($board, 'deleted', 'task', $taskId);
    }
}
