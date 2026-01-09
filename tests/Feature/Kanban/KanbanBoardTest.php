<?php

declare(strict_types=1);

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('Kanban Boards', function () {
    test('user can list their boards', function () {
        $user = User::factory()->create();
        KanbanBoard::factory()->count(2)->create(['user_id' => $user->id]);
        KanbanBoard::factory()->create();

        $response = $this->actingAs($user)->getJson(route('kanban.boards.index'));

        $response->assertOk();

        expect($response->json('boards'))->toHaveCount(2);
    });

    test('user cannot see other users boards', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        KanbanBoard::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->getJson(route('kanban.boards.index'));

        $response->assertOk();

        expect($response->json('boards'))->toHaveCount(0);
    });

    test('user can create a board', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('kanban.boards.store'), [
            'name' => 'My Project Board',
            'description' => 'A board for tracking my project',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('board.name'))->toBe('My Project Board');
        expect($response->json('board.description'))->toBe('A board for tracking my project');

        $this->assertDatabaseHas('kanban_boards', [
            'user_id' => $user->id,
            'name' => 'My Project Board',
        ]);
    });

    test('board creation requires name', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('kanban.boards.store'), [
            'description' => 'A board without a name',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.name'))->not->toBeNull();
    });

    test('user can view their board with columns and tasks', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->getJson(route('kanban.boards.show', $board));

        $response->assertOk();

        expect($response->json('board.id'))->toBe($board->id);
        expect($response->json('board.columns'))->toHaveCount(1);
        expect($response->json('board.columns.0.tasks'))->toHaveCount(1);
    });

    test('user cannot view other users board', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->getJson(route('kanban.boards.show', $board));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can update their board', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $response = $this->actingAs($user)->patchJson(route('kanban.boards.update', $board), [
            'name' => 'New Name',
        ]);

        $response->assertOk();

        expect($response->json('board.name'))->toBe('New Name');

        $this->assertDatabaseHas('kanban_boards', [
            'id' => $board->id,
            'name' => 'New Name',
        ]);
    });

    test('user cannot update other users board', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);

        $response = $this->actingAs($userB)->patchJson(route('kanban.boards.update', $board), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their board', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson(route('kanban.boards.destroy', $board));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_boards', ['id' => $board->id]);
    });

    test('deleting board cascades to columns and tasks', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->deleteJson(route('kanban.boards.destroy', $board));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_boards', ['id' => $board->id]);
        $this->assertDatabaseMissing('kanban_columns', ['id' => $column->id]);
        $this->assertDatabaseMissing('kanban_tasks', ['id' => $task->id]);
    });

    test('unauthenticated user cannot access boards', function () {
        $response = $this->getJson(route('kanban.boards.index'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
})->group('kanban', 'kanban-boards');
