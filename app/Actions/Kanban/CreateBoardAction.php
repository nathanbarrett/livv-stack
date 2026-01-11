<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanBoard;
use App\Models\User;

class CreateBoardAction
{
    public function handle(
        User $user,
        string $name,
        ?string $description = null,
        ?string $projectName = null,
        ?int $copyColumnsFromBoardId = null,
    ): KanbanBoard {
        $board = $user->kanbanBoards()->create([
            'name' => $name,
            'description' => $description,
            'project_name' => $projectName,
        ]);

        if ($copyColumnsFromBoardId) {
            $sourceBoard = KanbanBoard::where('id', $copyColumnsFromBoardId)
                ->where('user_id', $user->id)
                ->first();

            if ($sourceBoard) {
                foreach ($sourceBoard->columns()->orderBy('position')->get() as $index => $column) {
                    $board->columns()->create([
                        'name' => $column->name,
                        'color' => $column->color,
                        'description' => $column->description,
                        'position' => $index,
                    ]);
                }
            }
        }

        return $board->load('columns');
    }
}
