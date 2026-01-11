<?php

declare(strict_types=1);

namespace App\AI\RealtimeTools;

use App\Actions\UserMemory\DeleteMemoryAction;
use App\Actions\UserMemory\ListMemoriesAction;
use App\Actions\UserMemory\ListMemoryTypesAction;
use App\Actions\UserMemory\SaveMemoryAction;
use App\Models\User;

class RealtimeMemoryTool
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function execute(User $user, array $args): string
    {
        $action = $args['action'] ?? null;

        return match ($action) {
            'list_types' => $this->listTypes($user),
            'list_memories' => $this->listMemories($user, $args['type'] ?? null),
            'save' => $this->saveMemory($user, $args['type'] ?? null, $args['key'] ?? null, $args['value'] ?? null),
            'delete' => $this->deleteMemory($user, $args['type'] ?? null, $args['key'] ?? null),
            default => "Unknown action: {$action}. Valid actions are: list_types, list_memories, save, delete.",
        };
    }

    private function listTypes(User $user): string
    {
        return app(ListMemoryTypesAction::class)->handle($user);
    }

    private function listMemories(User $user, ?string $type): string
    {
        if ($type === null) {
            return 'Error: type parameter is required for list_memories action.';
        }

        return app(ListMemoriesAction::class)->handle($user, $type);
    }

    private function saveMemory(User $user, ?string $type, ?string $key, ?string $value): string
    {
        if ($type === null || $key === null || $value === null) {
            return 'Error: type, key, and value parameters are required for save action.';
        }

        return app(SaveMemoryAction::class)->handle($user, $type, $key, $value);
    }

    private function deleteMemory(User $user, ?string $type, ?string $key): string
    {
        if ($type === null || $key === null) {
            return 'Error: type and key parameters are required for delete action.';
        }

        return app(DeleteMemoryAction::class)->handle($user, $type, $key);
    }
}
