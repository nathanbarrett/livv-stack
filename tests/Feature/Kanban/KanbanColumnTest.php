<?php

declare(strict_types=1);

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('Kanban Columns', function () {
    test('user can create column on their board', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('kanban.columns.store', $board), [
            'name' => 'To Do',
            'color' => '#ff0000',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('column.name'))->toBe('To Do');
        expect($response->json('column.color'))->toBe('#ff0000');
        expect($response->json('column.kanban_board_id'))->toBe($board->id);

        $this->assertDatabaseHas('kanban_columns', [
            'kanban_board_id' => $board->id,
            'name' => 'To Do',
        ]);
    });

    test('user cannot create column on other users board', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->postJson(route('kanban.columns.store', $board), [
            'name' => 'Hacked Column',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('column creation requires name', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('kanban.columns.store', $board), [
            'color' => '#ff0000',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.name'))->not->toBeNull();
    });

    test('new columns are added at the end', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 0]);
        KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 1]);

        $response = $this->actingAs($user)->postJson(route('kanban.columns.store', $board), [
            'name' => 'New Column',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('column.position'))->toBe(2);
    });

    test('user can update their column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'name' => 'Old Name']);

        $response = $this->actingAs($user)->patchJson(route('kanban.columns.update', $column), [
            'name' => 'New Name',
            'color' => '#00ff00',
        ]);

        $response->assertOk();

        expect($response->json('column.name'))->toBe('New Name');
        expect($response->json('column.color'))->toBe('#00ff00');
    });

    test('user cannot update other users column', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($userB)->patchJson(route('kanban.columns.update', $column), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their column', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);

        $response = $this->actingAs($user)->deleteJson(route('kanban.columns.destroy', $column));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_columns', ['id' => $column->id]);
    });

    test('deleting column cascades to tasks', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->deleteJson(route('kanban.columns.destroy', $column));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_columns', ['id' => $column->id]);
        $this->assertDatabaseMissing('kanban_tasks', ['id' => $task->id]);
    });

    test('user can move column to new position', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 0, 'name' => 'First']);
        $column2 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 1, 'name' => 'Second']);
        $column3 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 2, 'name' => 'Third']);

        $response = $this->actingAs($user)->patchJson(route('kanban.columns.move', $column3), [
            'position' => 0,
        ]);

        $response->assertOk();

        expect($response->json('column.position'))->toBe(0);

        expect($column1->fresh()->position)->toBe(1);
        expect($column2->fresh()->position)->toBe(2);
        expect($column3->fresh()->position)->toBe(0);
    });

    test('moving column forward updates positions correctly', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column1 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 0]);
        $column2 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 1]);
        $column3 = KanbanColumn::factory()->create(['kanban_board_id' => $board->id, 'position' => 2]);

        $response = $this->actingAs($user)->patchJson(route('kanban.columns.move', $column1), [
            'position' => 2,
        ]);

        $response->assertOk();

        expect($column1->fresh()->position)->toBe(2);
        expect($column2->fresh()->position)->toBe(0);
        expect($column3->fresh()->position)->toBe(1);
    });
})->group('kanban', 'kanban-columns');
