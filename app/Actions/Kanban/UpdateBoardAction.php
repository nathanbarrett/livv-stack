<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanBoard;

class UpdateBoardAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(KanbanBoard $board, array $data): KanbanBoard
    {
        $board->update($data);
        $board = $board->fresh();

        KanbanBoardUpdated::dispatch($board, 'updated', 'board', $board->id);

        return $board;
    }
}
