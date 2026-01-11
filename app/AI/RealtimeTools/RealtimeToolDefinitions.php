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
            'description' => 'Full kanban management: boards, columns, tasks, links, dependencies, notes, and attachments. Markdown supported in descriptions, implementation_plans, and notes.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => [
                            // Board actions
                            'list_boards',
                            'create_board',
                            'update_board',
                            'delete_board',
                            // Column actions
                            'list_columns',
                            'create_column',
                            'update_column',
                            'move_column',
                            'delete_column',
                            // Task actions
                            'list_tasks',
                            'create_task',
                            'update_task',
                            'move_task',
                            'delete_task',
                            'show_task_implementation_plan',
                            // Note actions
                            'show_task_notes',
                            'add_task_note',
                            'update_task_note',
                            'delete_task_note',
                            // Link actions
                            'add_task_link',
                            'remove_task_link',
                            // Dependency actions
                            'add_task_dependency',
                            'remove_task_dependency',
                            // Attachment actions
                            'list_task_attachments',
                            'delete_task_attachment',
                        ],
                        'description' => 'The action to perform',
                    ],
                    // Board parameters
                    'board_id' => [
                        'type' => 'number',
                        'description' => 'Board ID (for: list_columns, list_tasks, update_board, delete_board, create_column)',
                    ],
                    'name' => [
                        'type' => 'string',
                        'description' => 'Name (for: create_board, update_board, create_column, update_column)',
                    ],
                    'project_name' => [
                        'type' => 'string',
                        'description' => 'Project name (for: create_board, update_board)',
                    ],
                    // Column parameters
                    'column_id' => [
                        'type' => 'number',
                        'description' => 'Column ID (for: list_tasks, create_task, update_column, move_column, delete_column, move_task)',
                    ],
                    'color' => [
                        'type' => 'string',
                        'description' => 'Hex color code (for: create_column, update_column)',
                    ],
                    // Task parameters
                    'task_id' => [
                        'type' => 'number',
                        'description' => 'Task ID (for: task operations, notes, links, dependencies)',
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Task title (for: create_task, update_task)',
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'Description, markdown supported (for: create_board, update_board, create_task, update_task, create_column, update_column)',
                    ],
                    'implementation_plans' => [
                        'type' => 'string',
                        'description' => 'Implementation plans, markdown supported (for: create_task, update_task)',
                    ],
                    'due_date' => [
                        'type' => 'string',
                        'description' => 'Due date YYYY-MM-DD (for: create_task, update_task, "clear" to remove)',
                    ],
                    'priority' => [
                        'type' => 'string',
                        'description' => 'Priority: low, medium, high, or "clear" to remove',
                    ],
                    'position' => [
                        'type' => 'number',
                        'description' => 'Position (for: move_task, move_column, create_task)',
                    ],
                    // Note parameters
                    'note_id' => [
                        'type' => 'number',
                        'description' => 'Note ID (for: update_task_note, delete_task_note)',
                    ],
                    'note' => [
                        'type' => 'string',
                        'description' => 'Note content, markdown supported (for: add_task_note, update_task_note)',
                    ],
                    // Link parameters
                    'url' => [
                        'type' => 'string',
                        'description' => 'URL (for: add_task_link)',
                    ],
                    'label' => [
                        'type' => 'string',
                        'description' => 'Link label (for: add_task_link)',
                    ],
                    'link_index' => [
                        'type' => 'number',
                        'description' => 'Link index 0-based (for: remove_task_link)',
                    ],
                    // Dependency parameters
                    'depends_on_task_id' => [
                        'type' => 'number',
                        'description' => 'Dependency task ID (for: add_task_dependency, remove_task_dependency)',
                    ],
                    // Attachment parameters
                    'attachment_id' => [
                        'type' => 'number',
                        'description' => 'Attachment ID (for: delete_task_attachment)',
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
