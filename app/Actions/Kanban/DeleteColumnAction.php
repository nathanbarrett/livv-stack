<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanColumn;

class DeleteColumnAction
{
    public function handle(KanbanColumn $column): void
    {
        $board = $column->board;
        $columnId = $column->id;

        $column->delete();

        KanbanBoardUpdated::dispatch($board, 'deleted', 'column', $columnId);
    }
}
