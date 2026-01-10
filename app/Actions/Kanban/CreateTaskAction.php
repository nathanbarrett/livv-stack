<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Enums\KanbanTaskPriority;
use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Carbon\Carbon;

class CreateTaskAction
{
    public function handle(
        KanbanColumn $column,
        string $title,
        ?string $description = null,
        ?string $implementationPlans = null,
        ?Carbon $dueDate = null,
        ?KanbanTaskPriority $priority = null,
    ): KanbanTask {
        $maxPosition = $column->tasks()->max('position') ?? -1;

        $task = $column->tasks()->create([
            'title' => $title,
            'description' => $description,
            'implementation_plans' => $implementationPlans,
            'due_date' => $dueDate,
            'priority' => $priority?->value,
            'position' => $maxPosition + 1,
        ]);

        KanbanBoardUpdated::dispatch($column->board, 'created', 'task', $task->id);

        return $task;
    }
}
