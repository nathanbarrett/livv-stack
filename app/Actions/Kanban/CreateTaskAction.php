<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Enums\KanbanTaskPriority;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Carbon\Carbon;

class CreateTaskAction
{
    public function handle(
        KanbanColumn $column,
        string $title,
        ?string $description = null,
        ?Carbon $dueDate = null,
        ?KanbanTaskPriority $priority = null,
    ): KanbanTask {
        $maxPosition = $column->tasks()->max('position') ?? -1;

        return $column->tasks()->create([
            'title' => $title,
            'description' => $description,
            'due_date' => $dueDate,
            'priority' => $priority?->value,
            'position' => $maxPosition + 1,
        ]);
    }
}
