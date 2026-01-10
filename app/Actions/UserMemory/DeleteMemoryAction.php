<?php

declare(strict_types=1);

namespace App\Actions\UserMemory;

use App\Models\User;
use App\Models\UserMemory;

class DeleteMemoryAction
{
    public function handle(User $user, string $type, string $key): string
    {
        $deleted = UserMemory::where('user_id', $user->id)
            ->where('type', $type)
            ->where('key', $key)
            ->delete();

        if ($deleted === 0) {
            return "No memory found with type '{$type}' and key '{$key}'.";
        }

        return "Deleted memory [{$type}] {$key}.";
    }
}
