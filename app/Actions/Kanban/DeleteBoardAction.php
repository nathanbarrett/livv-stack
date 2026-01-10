<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanBoard;

class DeleteBoardAction
{
    public function handle(KanbanBoard $board): void
    {
        $boardId = $board->id;

        KanbanBoardUpdated::dispatch($board, 'deleted', 'board', $boardId);

        $board->delete();
    }
}
