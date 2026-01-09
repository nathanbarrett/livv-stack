<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanBoard;
use App\Models\User;

class CreateBoardAction
{
    public function handle(User $user, string $name, ?string $description = null): KanbanBoard
    {
        return $user->kanbanBoards()->create([
            'name' => $name,
            'description' => $description,
        ]);
    }
}
