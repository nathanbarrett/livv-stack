<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KanbanBoard;
use App\Models\User;

class KanbanBoardPolicy
{
    public function view(User $user, KanbanBoard $board): bool
    {
        return $user->id === $board->user_id;
    }

    public function update(User $user, KanbanBoard $board): bool
    {
        return $user->id === $board->user_id;
    }

    public function delete(User $user, KanbanBoard $board): bool
    {
        return $user->id === $board->user_id;
    }
}
