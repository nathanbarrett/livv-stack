<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanColumn;

class UpdateColumnAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(KanbanColumn $column, array $data): KanbanColumn
    {
        $column->update($data);

        return $column->fresh();
    }
}
