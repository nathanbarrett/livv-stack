<?php

declare(strict_types=1);

namespace App\Actions\UserMemory;

use App\Models\User;
use App\Models\UserMemory;

class ListMemoryTypesAction
{
    public function handle(User $user): string
    {
        $types = UserMemory::where('user_id', $user->id)
            ->distinct()
            ->pluck('type')
            ->toArray();

        if (empty($types)) {
            return 'No memory types found. You can create memories with types like "personal", "preferences", "work", etc.';
        }

        return 'Available memory types: '.implode(', ', $types);
    }
}
