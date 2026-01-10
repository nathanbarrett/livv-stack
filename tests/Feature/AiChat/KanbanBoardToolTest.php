<?php

declare(strict_types=1);

use App\AI\Tools\KanbanBoardTool;
use App\Enums\KanbanTaskNoteAuthor;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\KanbanTaskNote;
use App\Models\User;

uses(Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('KanbanBoardTool - list_boards', function () {
    test('lists all boards for user', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Board',
            'project_name' => 'My Project',
        ]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        KanbanTask::factory()->count(3)->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_boards');

        expect($result)->toContain('Kanban Boards:');
        expect($result)->toContain("Board #{$board->id}: Test Board (Project: My Project)");
        expect($result)->toContain('Columns: 1 | Tasks: 3');
    });

    test('returns message when no boards exist', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_boards');

        expect($result)->toContain('No kanban boards found');
    });

    test('does not show other users boards', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        KanbanBoard::factory()->create(['user_id' => $userA->id, 'name' => 'User A Board']);
        KanbanBoard::factory()->create(['user_id' => $userB->id, 'name' => 'User B Board']);

        $tool = new KanbanBoardTool($userA);
        $result = $tool('list_boards');

        expect($result)->toContain('User A Board');
        expect($result)->not->toContain('User B Board');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - list_columns', function () {
    test('lists columns for a board', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id, 'name' => 'Test Board']);
        $column1 = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
            'position' => 0,
            'color' => '#FF0000',
        ]);
        $column2 = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'Done',
            'position' => 1,
        ]);
        KanbanTask::factory()->count(2)->create(['kanban_column_id' => $column1->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_columns', boardId: $board->id);

        expect($result)->toContain("Columns in Board #{$board->id} (Test Board)");
        expect($result)->toContain("Column #{$column1->id}: To Do");
        expect($result)->toContain('[Color: #FF0000]');
        expect($result)->toContain("Column #{$column2->id}: Done");
        expect($result)->toContain('Tasks: 2');
    });

    test('returns error when board_id not provided', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_columns');

        expect($result)->toContain('Error: board_id parameter is required');
    });

    test('returns error for non-existent board', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_columns', boardId: 99999);

        expect($result)->toContain('Error: Board #99999 not found');
    });

    test('cannot list columns of other users board', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);

        $tool = new KanbanBoardTool($userB);
        $result = $tool('list_columns', boardId: $board->id);

        expect($result)->toContain('Error:');
        expect($result)->toContain("don't have access");
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - list_tasks', function () {
    test('lists tasks in a column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
        ]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'My Task',
            'description' => 'A task description',
            'priority' => 'high',
            'due_date' => '2025-12-31',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_tasks', boardId: $board->id, columnId: $column->id);

        expect($result)->toContain("Tasks in Column #{$column->id} (To Do)");
        expect($result)->toContain("Task #{$task->id}: My Task");
        expect($result)->toContain('[Priority: high]');
        expect($result)->toContain('[Due: 2025-12-31]');
        expect($result)->toContain('Description: A task description');
        expect($result)->toContain('show_task_implementation_plan');
        expect($result)->toContain('show_task_notes');
    });

    test('returns error when parameters missing', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('list_tasks');

        expect($result)->toContain('Error: board_id and column_id parameters are required');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - show_task_implementation_plan', function () {
    test('shows implementation plan', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'My Task',
            'implementation_plans' => '## Step 1\nDo something\n\n## Step 2\nDo more',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('show_task_implementation_plan', taskId: $task->id);

        expect($result)->toContain("Implementation Plan for Task #{$task->id}");
        expect($result)->toContain('## Step 1');
        expect($result)->toContain('Do something');
    });

    test('returns message when no implementation plan exists', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'implementation_plans' => null,
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('show_task_implementation_plan', taskId: $task->id);

        expect($result)->toContain('has no implementation plan');
    });

    test('cannot view other users task implementation plan', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($userB);
        $result = $tool('show_task_implementation_plan', taskId: $task->id);

        expect($result)->toContain('Error:');
        expect($result)->toContain("don't have access");
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - show_task_notes', function () {
    test('shows task notes', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'My Task',
        ]);

        KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'User note content',
            'author' => 'user',
        ]);

        KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'AI note content',
            'author' => 'ai',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('show_task_notes', taskId: $task->id);

        expect($result)->toContain("Notes for Task #{$task->id}");
        expect($result)->toContain('[User -');
        expect($result)->toContain('[AI -');
        expect($result)->toContain('User note content');
        expect($result)->toContain('AI note content');
    });

    test('returns message when no notes exist', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('show_task_notes', taskId: $task->id);

        expect($result)->toContain('has no notes');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - move_task', function () {
    test('moves task to another column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
        ]);
        $column2 = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'Done',
        ]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column1->id,
            'title' => 'My Task',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('move_task', taskId: $task->id, columnId: $column2->id);

        expect($result)->toContain("Task #{$task->id} (My Task) moved to Column #{$column2->id} (Done)");

        $task->refresh();

        expect($task->kanban_column_id)->toBe($column2->id);
    });

    test('cannot move task to column in different board', function () {
        $user = User::factory()->create();
        $board1 = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $board2 = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create(['kanban_board_id' => $board1->id]);
        $column2 = KanbanColumn::factory()->create(['kanban_board_id' => $board2->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column1->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('move_task', taskId: $task->id, columnId: $column2->id);

        expect($result)->toContain('Error: Cannot move task to a column in a different board');
    });

    test('returns error when required parameters missing', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('move_task');

        expect($result)->toContain('Error: task_id and column_id parameters are required');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - add_task_note', function () {
    test('adds AI note to task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'My Task',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('add_task_note', taskId: $task->id, note: 'This is an AI generated note');

        expect($result)->toContain("Note added to Task #{$task->id}");
        expect($result)->toContain('Note ID: #');
        expect($result)->toContain('This is an AI generated note');

        $note = $task->notes()->first();

        expect($note)->not->toBeNull();
        expect($note->author)->toBe(KanbanTaskNoteAuthor::Ai);
        expect($note->note)->toBe('This is an AI generated note');
    });

    test('returns error when parameters missing', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('add_task_note');

        expect($result)->toContain('Error: task_id and note parameters are required');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - update_task', function () {
    test('updates task title', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'Original Title',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, title: 'New Title');

        expect($result)->toContain("Task #{$task->id} updated successfully");
        expect($result)->toContain('title to "New Title"');

        $task->refresh();

        expect($task->title)->toBe('New Title');
    });

    test('updates task priority', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'priority' => null,
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, priority: 'high');

        expect($result)->toContain('priority to high');

        $task->refresh();

        expect($task->priority->value)->toBe('high');
    });

    test('clears priority with clear value', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'priority' => 'high',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, priority: 'clear');

        expect($result)->toContain('priority cleared');

        $task->refresh();

        expect($task->priority)->toBeNull();
    });

    test('updates due date', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, dueDate: '2025-06-15');

        expect($result)->toContain('due_date to 2025-06-15');

        $task->refresh();

        expect($task->due_date->format('Y-m-d'))->toBe('2025-06-15');
    });

    test('clears due date with clear value', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'due_date' => '2025-06-15',
        ]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, dueDate: 'clear');

        expect($result)->toContain('due_date cleared');

        $task->refresh();

        expect($task->due_date)->toBeNull();
    });

    test('returns error for invalid priority', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, priority: 'invalid');

        expect($result)->toContain("Error: Invalid priority 'invalid'");
    });

    test('returns error for invalid date format', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id, dueDate: 'not-a-date');

        expect($result)->toContain('Error: Invalid date format');
    });

    test('returns error when no fields provided', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($user);
        $result = $tool('update_task', taskId: $task->id);

        expect($result)->toContain('Error: No fields provided to update');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - delete_task', function () {
    test('deletes task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id, 'name' => 'Test Board']);
        $column = KanbanColumn::factory()->create([
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
        ]);
        $task = KanbanTask::factory()->create([
            'kanban_column_id' => $column->id,
            'title' => 'Task to Delete',
        ]);

        $taskId = $task->id;

        $tool = new KanbanBoardTool($user);
        $result = $tool('delete_task', taskId: $taskId);

        expect($result)->toContain("Task #{$taskId} (Task to Delete) deleted");
        expect($result)->toContain('Column "To Do"');
        expect($result)->toContain('Board "Test Board"');

        expect(KanbanTask::find($taskId))->toBeNull();
    });

    test('cannot delete other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $tool = new KanbanBoardTool($userB);
        $result = $tool('delete_task', taskId: $task->id);

        expect($result)->toContain('Error:');
        expect($result)->toContain("don't have access");

        expect(KanbanTask::find($task->id))->not->toBeNull();
    });

    test('returns error when task_id not provided', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('delete_task');

        expect($result)->toContain('Error: task_id parameter is required');
    });
})->group('ai-chat', 'kanban-board-tool');

describe('KanbanBoardTool - unknown action', function () {
    test('returns error for unknown action', function () {
        $user = User::factory()->create();

        $tool = new KanbanBoardTool($user);
        $result = $tool('invalid_action');

        expect($result)->toContain('Unknown action: invalid_action');
    });
})->group('ai-chat', 'kanban-board-tool');
