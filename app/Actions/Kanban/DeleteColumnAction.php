<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanColumn;

class DeleteColumnAction
{
    public function handle(KanbanColumn $column): void
    {
        $column->delete();
    }
}
