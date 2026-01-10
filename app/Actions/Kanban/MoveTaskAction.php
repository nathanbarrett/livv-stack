<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanTask;
use Illuminate\Support\Facades\DB;

class MoveTaskAction
{
    public function handle(KanbanTask $task, int $targetColumnId, int $newPosition): KanbanTask
    {
        $oldColumnId = $task->kanban_column_id;
        $oldPosition = $task->position;
        $isSameColumn = $oldColumnId === $targetColumnId;

        DB::transaction(function () use ($task, $oldColumnId, $oldPosition, $targetColumnId, $newPosition, $isSameColumn): void {
            if ($isSameColumn) {
                if ($newPosition === $oldPosition) {
                    return;
                }

                if ($newPosition > $oldPosition) {
                    KanbanTask::where('kanban_column_id', $oldColumnId)
                        ->where('position', '>', $oldPosition)
                        ->where('position', '<=', $newPosition)
                        ->decrement('position');
                } else {
                    KanbanTask::where('kanban_column_id', $oldColumnId)
                        ->where('position', '>=', $newPosition)
                        ->where('position', '<', $oldPosition)
                        ->increment('position');
                }

                $task->update(['position' => $newPosition]);

                return;
            }

            KanbanTask::where('kanban_column_id', $oldColumnId)
                ->where('position', '>', $oldPosition)
                ->decrement('position');

            KanbanTask::where('kanban_column_id', $targetColumnId)
                ->where('position', '>=', $newPosition)
                ->increment('position');

            $task->update([
                'kanban_column_id' => $targetColumnId,
                'position' => $newPosition,
            ]);
        });

        $task = $task->fresh();

        KanbanBoardUpdated::dispatch($task->column->board, 'moved', 'task', $task->id);

        return $task;
    }
}
