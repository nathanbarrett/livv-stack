<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTask;

class UpdateTaskAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(KanbanTask $task, array $data): KanbanTask
    {
        $task->update($data);

        return $task->fresh();
    }
}
