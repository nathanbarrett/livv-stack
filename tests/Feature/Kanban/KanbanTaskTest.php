<?php

declare(strict_types=1);

use App\Enums\KanbanTaskPriority;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('Kanban Tasks', function () {
    test('user can create task in column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.tasks.store', $column), [
            'title' => 'My First Task',
            'description' => 'Task description',
            'priority' => 'high',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('task.title'))->toBe('My First Task');
        expect($response->json('task.description'))->toBe('Task description');
        expect($response->json('task.priority'))->toBe('high');
        expect($response->json('task.kanban_column_id'))->toBe($column->id);

        $this->assertDatabaseHas('kanban_tasks', [
            'kanban_column_id' => $column->id,
            'title' => 'My First Task',
        ]);
    });

    test('user cannot create task in other users column', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($userB)->postJson(route('api.kanban.tasks.store', $column), [
            'title' => 'Hacked Task',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('task creation requires title', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.tasks.store', $column), [
            'description' => 'Task without title',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.title'))->not->toBeNull();
    });

    test('task can be created with priority', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.tasks.store', $column), [
            'title' => 'High Priority Task',
            'priority' => 'high',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $task = KanbanTask::find($response->json('task.id'));

        expect($task->priority)->toBe(KanbanTaskPriority::High);
    });

    test('task priority validation rejects invalid values', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.tasks.store', $column), [
            'title' => 'Task with Invalid Priority',
            'priority' => 'invalid',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.priority'))->not->toBeNull();
    });

    test('new tasks are added at the end', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'position' => 0]);
        KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'position' => 1]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.tasks.store', $column), [
            'title' => 'New Task',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('task.position'))->toBe(2);
    });

    test('user can update their task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'title' => 'Old Title']);

        $response = $this->actingAs($user)->patchJson(route('api.kanban.tasks.update', $task), [
            'title' => 'New Title',
            'priority' => 'medium',
        ]);

        $response->assertOk();

        expect($response->json('task.title'))->toBe('New Title');
        expect($response->json('task.priority'))->toBe('medium');
    });

    test('user cannot update other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($userB)->patchJson(route('api.kanban.tasks.update', $task), [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.kanban.tasks.destroy', $task));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_tasks', ['id' => $task->id]);
    });

    test('user can move task within same column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task1 = KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'position' => 0]);
        $task2 = KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'position' => 1]);
        $task3 = KanbanTask::factory()->create(['kanban_column_id' => $column->id, 'position' => 2]);

        $response = $this->actingAs($user)->patchJson(route('api.kanban.tasks.move', $task3), [
            'kanban_column_id' => $column->id,
            'position' => 0,
        ]);

        $response->assertOk();

        expect($task1->fresh()->position)->toBe(1);
        expect($task2->fresh()->position)->toBe(2);
        expect($task3->fresh()->position)->toBe(0);
    });

    test('user can move task to different column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $column2 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column1->id, 'position' => 0]);

        $response = $this->actingAs($user)->patchJson(route('api.kanban.tasks.move', $task), [
            'kanban_column_id' => $column2->id,
            'position' => 0,
        ]);

        $response->assertOk();

        expect($task->fresh()->kanban_column_id)->toBe($column2->id);
        expect($task->fresh()->position)->toBe(0);
    });

    test('moving task to different column updates positions in both columns', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $column2 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $task1 = KanbanTask::factory()->create(['kanban_column_id' => $column1->id, 'position' => 0]);
        $task2 = KanbanTask::factory()->create(['kanban_column_id' => $column1->id, 'position' => 1]);

        $task3 = KanbanTask::factory()->create(['kanban_column_id' => $column2->id, 'position' => 0]);
        $task4 = KanbanTask::factory()->create(['kanban_column_id' => $column2->id, 'position' => 1]);

        $response = $this->actingAs($user)->patchJson(route('api.kanban.tasks.move', $task1), [
            'kanban_column_id' => $column2->id,
            'position' => 1,
        ]);

        $response->assertOk();

        expect($task1->fresh()->kanban_column_id)->toBe($column2->id);
        expect($task1->fresh()->position)->toBe(1);

        expect($task2->fresh()->position)->toBe(0);

        expect($task3->fresh()->position)->toBe(0);
        expect($task4->fresh()->position)->toBe(2);
    });

    test('user cannot move task to column on different users board', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $boardA = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $boardB = KanbanBoard::factory()->create(['user_id' => $userB->id]);
        $columnA = KanbanColumn::factory()->create(['kanban_board_id' => $boardA->id]);
        $columnB = KanbanColumn::factory()->create(['kanban_board_id' => $boardB->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $columnA->id]);

        $response = $this->actingAs($userA)->patchJson(route('api.kanban.tasks.move', $task), [
            'kanban_column_id' => $columnB->id,
            'position' => 0,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
})->group('kanban', 'kanban-tasks');
