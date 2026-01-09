<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanBoard;

class UpdateBoardAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(KanbanBoard $board, array $data): KanbanBoard
    {
        $board->update($data);

        return $board->fresh();
    }
}
