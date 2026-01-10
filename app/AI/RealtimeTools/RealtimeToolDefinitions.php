<?php

declare(strict_types=1);

namespace App\AI\RealtimeTools;

class RealtimeToolDefinitions
{
    /**
     * @return array{
     *     type: string,
     *     name: string,
     *     description: string,
     *     parameters: array<string, mixed>
     * }
     */
    public static function getKanbanTool(): array
    {
        return [
            'type' => 'function',
            'name' => 'manage_kanban',
            'description' => 'Manage kanban boards, columns, and tasks. Use to view boards, list tasks, update tasks, move tasks between columns, view implementation plans, view/add notes, and delete tasks. Markdown is supported in description, implementation_plans, and notes fields.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => [
                            'list_boards',
                            'list_columns',
                            'list_tasks',
                            'show_task_implementation_plan',
                            'show_task_notes',
                            'move_task',
                            'add_task_note',
                            'update_task',
                            'delete_task',
                        ],
                        'description' => 'The action to perform',
                    ],
                    'board_id' => [
                        'type' => 'number',
                        'description' => 'Board ID (required for: list_columns, list_tasks)',
                    ],
                    'column_id' => [
                        'type' => 'number',
                        'description' => 'Column ID (required for: list_tasks; destination for: move_task)',
                    ],
                    'task_id' => [
                        'type' => 'number',
                        'description' => 'Task ID (required for: show_task_*, move_task, add_task_note, update_task, delete_task)',
                    ],
                    'position' => [
                        'type' => 'number',
                        'description' => 'Position in column (optional for: move_task, defaults to end)',
                    ],
                    'note' => [
                        'type' => 'string',
                        'description' => 'Note content, markdown supported (required for: add_task_note)',
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Task title (for: update_task)',
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'Task description, markdown supported (for: update_task)',
                    ],
                    'implementation_plans' => [
                        'type' => 'string',
                        'description' => 'Implementation plans, markdown supported (for: update_task)',
                    ],
                    'due_date' => [
                        'type' => 'string',
                        'description' => 'Due date YYYY-MM-DD format (for: update_task, use "clear" to remove)',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'description' => 'Priority: low, medium, high, or "clear" to remove (for: update_task)',
                    ],
                ],
                'required' => ['action'],
            ],
        ];
    }

    /**
     * @return array{
     *     type: string,
     *     name: string,
     *     description: string,
     *     parameters: array<string, mixed>
     * }
     */
    public static function getUserMemoryTool(): array
    {
        return [
            'type' => 'function',
            'name' => 'manage_user_memory',
            'description' => 'Manage memories about the user. Use this to remember important information the user shares, recall previously stored information, and update or delete outdated information. Use type "personal" for personal details like name, birthday, location. Create other types as needed (preferences, work, interests, etc.).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => ['list_types', 'list_memories', 'save', 'delete'],
                        'description' => 'The action to perform',
                    ],
                    'type' => [
                        'type' => 'string',
                        'description' => 'Memory type (e.g., "personal", "preferences", "work"). Required for list_memories, save, and delete actions.',
                    ],
                    'key' => [
                        'type' => 'string',
                        'description' => 'Memory key within the type (e.g., "name", "birthday", "residence", "favorite_color"). Required for save and delete actions.',
                    ],
                    'value' => [
                        'type' => 'string',
                        'description' => 'The memory content to store. Required for save action.',
                    ],
                ],
                'required' => ['action'],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     type: string,
     *     name: string,
     *     description: string,
     *     parameters: array<string, mixed>
     * }>
     */
    public static function all(): array
    {
        return [
            self::getKanbanTool(),
            self::getUserMemoryTool(),
        ];
    }
}
