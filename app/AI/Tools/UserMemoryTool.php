<?php

declare(strict_types=1);

namespace App\AI\Tools;

use App\Actions\UserMemory\DeleteMemoryAction;
use App\Actions\UserMemory\ListMemoriesAction;
use App\Actions\UserMemory\ListMemoryTypesAction;
use App\Actions\UserMemory\SaveMemoryAction;
use App\Models\User;
use Prism\Prism\Tool;

class UserMemoryTool extends Tool
{
    public function __construct(
        private User $user
    ) {
        $this
            ->as('manage_user_memory')
            ->for('Manage memories about the user. Use this to remember important information the user shares, recall previously stored information, and update or delete outdated information. Use type "personal" for personal details like name, birthday, location. Create other types as needed (preferences, work, interests, etc.).')
            ->withEnumParameter(
                'action',
                'The action to perform',
                ['list_types', 'list_memories', 'save', 'delete']
            )
            ->withStringParameter(
                'type',
                'Memory type (e.g., "personal", "preferences", "work"). Required for list_memories, save, and delete actions.'
            )
            ->withStringParameter(
                'key',
                'Memory key within the type (e.g., "name", "birthday", "residence", "favorite_color"). Required for save and delete actions.'
            )
            ->withStringParameter(
                'value',
                'The memory content to store. Required for save action.'
            )
            ->using($this);
    }

    public function __invoke(
        string $action,
        ?string $type = null,
        ?string $key = null,
        ?string $value = null
    ): string {
        return match ($action) {
            'list_types' => $this->listTypes(),
            'list_memories' => $this->listMemories($type),
            'save' => $this->saveMemory($type, $key, $value),
            'delete' => $this->deleteMemory($type, $key),
            default => "Unknown action: {$action}. Valid actions are: list_types, list_memories, save, delete.",
        };
    }

    private function listTypes(): string
    {
        return app(ListMemoryTypesAction::class)->handle($this->user);
    }

    private function listMemories(?string $type): string
    {
        if ($type === null) {
            return 'Error: type parameter is required for list_memories action.';
        }

        return app(ListMemoriesAction::class)->handle($this->user, $type);
    }

    private function saveMemory(?string $type, ?string $key, ?string $value): string
    {
        if ($type === null || $key === null || $value === null) {
            return 'Error: type, key, and value parameters are required for save action.';
        }

        return app(SaveMemoryAction::class)->handle($this->user, $type, $key, $value);
    }

    private function deleteMemory(?string $type, ?string $key): string
    {
        if ($type === null || $key === null) {
            return 'Error: type and key parameters are required for delete action.';
        }

        return app(DeleteMemoryAction::class)->handle($this->user, $type, $key);
    }
}
