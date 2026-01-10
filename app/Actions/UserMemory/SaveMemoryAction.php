<?php

declare(strict_types=1);

namespace App\Actions\UserMemory;

use App\Models\User;
use App\Models\UserMemory;

class SaveMemoryAction
{
    public function handle(User $user, string $type, string $key, string $value): string
    {
        $memory = UserMemory::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );

        $action = $memory->wasRecentlyCreated ? 'Saved' : 'Updated';

        return "{$action} memory [{$type}] {$key}: {$value}";
    }
}
