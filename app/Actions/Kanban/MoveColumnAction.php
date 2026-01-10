<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanColumn;
use Illuminate\Support\Facades\DB;

class MoveColumnAction
{
    public function handle(KanbanColumn $column, int $newPosition): KanbanColumn
    {
        $oldPosition = $column->position;
        $boardId = $column->kanban_board_id;

        if ($oldPosition === $newPosition) {
            return $column;
        }

        DB::transaction(function () use ($column, $oldPosition, $newPosition, $boardId): void {
            if ($newPosition > $oldPosition) {
                KanbanColumn::where('kanban_board_id', $boardId)
                    ->where('position', '>', $oldPosition)
                    ->where('position', '<=', $newPosition)
                    ->decrement('position');
            } else {
                KanbanColumn::where('kanban_board_id', $boardId)
                    ->where('position', '>=', $newPosition)
                    ->where('position', '<', $oldPosition)
                    ->increment('position');
            }

            $column->update(['position' => $newPosition]);
        });

        $column = $column->fresh();

        KanbanBoardUpdated::dispatch($column->board, 'moved', 'column', $column->id);

        return $column;
    }
}
