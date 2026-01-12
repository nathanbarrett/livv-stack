<?php

declare(strict_types=1);

use App\Enums\KanbanTaskNoteAuthor;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use App\Models\KanbanTaskNote;
use App\Models\User;
use Illuminate\Http\Response;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('Kanban Task Notes', function () {
    test('user can fetch notes for their task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note1 = KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'First note',
            'author' => 'user',
        ]);
        $note2 = KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'Second note',
            'author' => 'ai',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.kanban.notes.index', $task));

        $response->assertOk();
        expect($response->json('notes'))->toHaveCount(2);
        expect($response->json('notes.0.note'))->toBe('First note');
        expect($response->json('notes.1.note'))->toBe('Second note');
    });

    test('user cannot fetch notes for other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);

        $response = $this->actingAs($userB)->getJson(route('api.kanban.notes.index', $task));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can add a note to their task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.notes.store', $task), [
            'note' => 'This is a test note',
            'author' => 'user',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('note.note'))->toBe('This is a test note');
        expect($response->json('note.author'))->toBe('user');
        expect($response->json('note.kanban_task_id'))->toBe($task->id);

        $this->assertDatabaseHas('kanban_task_notes', [
            'kanban_task_id' => $task->id,
            'note' => 'This is a test note',
            'author' => 'user',
        ]);
    });

    test('user can add an AI note to their task', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.notes.store', $task), [
            'note' => 'AI generated note',
            'author' => 'ai',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        expect($response->json('note.author'))->toBe('ai');

        $note = KanbanTaskNote::find($response->json('note.id'));

        expect($note->author)->toBe(KanbanTaskNoteAuthor::Ai);
    });

    test('user cannot add note to other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($userB)->postJson(route('api.kanban.notes.store', $task), [
            'note' => 'Hacked note',
            'author' => 'user',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('note creation requires note text', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.notes.store', $task), [
            'author' => 'user',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.note'))->not->toBeNull();
    });

    test('note creation requires valid author', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);

        $response = $this->actingAs($user)->postJson(route('api.kanban.notes.store', $task), [
            'note' => 'A note',
            'author' => 'invalid',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        expect($response->json('errors.author'))->not->toBeNull();
    });

    test('user can update their note', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note = KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'Original note',
        ]);

        $response = $this->actingAs($user)->patchJson(route('api.kanban.notes.update', $note), [
            'note' => 'Updated note',
        ]);

        $response->assertOk();

        expect($response->json('note.note'))->toBe('Updated note');

        $this->assertDatabaseHas('kanban_task_notes', [
            'id' => $note->id,
            'note' => 'Updated note',
        ]);
    });

    test('user cannot update note on other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note = KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);

        $response = $this->actingAs($userB)->patchJson(route('api.kanban.notes.update', $note), [
            'note' => 'Hacked note',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('user can delete their note', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note = KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);

        $response = $this->actingAs($user)->deleteJson(route('api.kanban.notes.destroy', $note));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('kanban_task_notes', ['id' => $note->id]);
    });

    test('user cannot delete note on other users task', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $userA->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note = KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);

        $response = $this->actingAs($userB)->deleteJson(route('api.kanban.notes.destroy', $note));

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    test('notes are preserved when task is soft deleted', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        $note1 = KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);
        $note2 = KanbanTaskNote::factory()->create(['kanban_task_id' => $task->id]);

        $this->actingAs($user)->deleteJson(route('api.kanban.tasks.destroy', $task));

        $this->assertSoftDeleted('kanban_tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('kanban_task_notes', ['id' => $note1->id]);
        $this->assertDatabaseHas('kanban_task_notes', ['id' => $note2->id]);
    });

    test('notes are included when fetching board', function () {
        $user = User::factory()->create();
        $board = KanbanBoard::factory()->create(['user_id' => $user->id]);
        $column = KanbanColumn::factory()->create(['kanban_board_id' => $board->id]);
        $task = KanbanTask::factory()->create(['kanban_column_id' => $column->id]);
        KanbanTaskNote::factory()->create([
            'kanban_task_id' => $task->id,
            'note' => 'Test note',
            'author' => 'user',
        ]);

        $response = $this->actingAs($user)->getJson(route('api.kanban.boards.index'));

        $response->assertOk();

        $notes = $response->json('boards.0.columns.0.tasks.0.notes');

        expect($notes)->toHaveCount(1);
        expect($notes[0]['note'])->toBe('Test note');
        expect($notes[0]['author'])->toBe('user');
    });
})->group('kanban', 'kanban-task-notes');
