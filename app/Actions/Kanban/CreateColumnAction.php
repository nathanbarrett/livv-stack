<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;

class CreateColumnAction
{
    public function handle(KanbanBoard $board, string $name, ?string $color = null): KanbanColumn
    {
        $maxPosition = $board->columns()->max('position') ?? -1;

        return $board->columns()->create([
            'name' => $name,
            'color' => $color,
            'position' => $maxPosition + 1,
        ]);
    }
}
