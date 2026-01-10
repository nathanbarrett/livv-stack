<?php

declare(strict_types=1);

namespace App\Actions\UserMemory;

use App\Models\User;
use App\Models\UserMemory;

class ListMemoriesAction
{
    public function handle(User $user, string $type): string
    {
        $memories = UserMemory::where('user_id', $user->id)
            ->where('type', $type)
            ->orderBy('key')
            ->get();

        if ($memories->isEmpty()) {
            return "No memories found for type '{$type}'.";
        }

        $lines = ["Memories for type '{$type}':"];

        foreach ($memories as $memory) {
            $lines[] = "- {$memory->key}: {$memory->value}";
        }

        return implode("\n", $lines);
    }
}
