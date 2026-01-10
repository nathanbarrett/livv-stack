<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Events\Kanban\KanbanBoardUpdated;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;

class CreateColumnAction
{
    public function handle(KanbanBoard $board, string $name, ?string $color = null): KanbanColumn
    {
        $maxPosition = $board->columns()->max('position') ?? -1;

        $column = $board->columns()->create([
            'name' => $name,
            'color' => $color,
            'position' => $maxPosition + 1,
        ]);

        KanbanBoardUpdated::dispatch($board, 'created', 'column', $column->id);

        return $column;
    }
}
