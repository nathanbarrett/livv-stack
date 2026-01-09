<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanBoard;

class DeleteBoardAction
{
    public function handle(KanbanBoard $board): void
    {
        $board->delete();
    }
}
