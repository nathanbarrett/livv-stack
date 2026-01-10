<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTask;

class SyncTaskDependenciesAction
{
    /**
     * Sync task dependencies, filtering to only tasks on the same board.
     *
     * @param  array<int>  $dependencyIds
     */
    public function handle(KanbanTask $task, array $dependencyIds): void
    {
        if (empty($dependencyIds)) {
            $task->dependencies()->detach();

            return;
        }

        $boardId = $task->column->kanban_board_id;

        $validIds = KanbanTask::whereIn('id', $dependencyIds)
            ->whereHas('column', fn ($query) => $query->where('kanban_board_id', $boardId))
            ->where('id', '!=', $task->id)
            ->pluck('id')
            ->toArray();

        $task->dependencies()->sync($validIds);
    }
}
