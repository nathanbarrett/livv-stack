<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ApiAiChatAttachmentsController;
use App\Http\Controllers\Api\ApiAiChatMessagesController;
use App\Http\Controllers\Api\ApiAiChatModelsController;
use App\Http\Controllers\Api\ApiAiChatSessionsController;
use App\Http\Controllers\Api\ApiKanbanBoardsController;
use App\Http\Controllers\Api\ApiKanbanColumnsController;
use App\Http\Controllers\Api\ApiKanbanTaskAttachmentsController;
use App\Http\Controllers\Api\ApiKanbanTaskNotesController;
use App\Http\Controllers\Api\ApiKanbanTasksController;
use App\Http\Controllers\Api\ApiRealtimeFunctionController;
use App\Http\Controllers\Api\ApiRealtimeTokenController;
use App\Http\Controllers\Api\ApiUserMemoriesController;
use Illuminate\Support\Facades\Route;

Route::prefix('kanban')
    ->middleware('auth')
    ->name('kanban.')
    ->group(function () {
        Route::get('/boards', [ApiKanbanBoardsController::class, 'index'])
            ->name('boards.index');
        Route::post('/boards', [ApiKanbanBoardsController::class, 'store'])
            ->name('boards.store');
        Route::get('/boards/{board}', [ApiKanbanBoardsController::class, 'show'])
            ->name('boards.show');
        Route::patch('/boards/{board}', [ApiKanbanBoardsController::class, 'update'])
            ->name('boards.update');
        Route::delete('/boards/{board}', [ApiKanbanBoardsController::class, 'destroy'])
            ->name('boards.destroy');
        Route::get('/boards/{board}/tasks', [ApiKanbanBoardsController::class, 'tasks'])
            ->name('boards.tasks');

        Route::post('/boards/{board}/columns', [ApiKanbanColumnsController::class, 'store'])
            ->name('columns.store');
        Route::patch('/columns/{column}', [ApiKanbanColumnsController::class, 'update'])
            ->name('columns.update');
        Route::delete('/columns/{column}', [ApiKanbanColumnsController::class, 'destroy'])
            ->name('columns.destroy');
        Route::patch('/columns/{column}/move', [ApiKanbanColumnsController::class, 'move'])
            ->name('columns.move');

        Route::post('/columns/{column}/tasks', [ApiKanbanTasksController::class, 'store'])
            ->name('tasks.store');
        Route::patch('/tasks/{task}', [ApiKanbanTasksController::class, 'update'])
            ->name('tasks.update');
        Route::delete('/tasks/{task}', [ApiKanbanTasksController::class, 'destroy'])
            ->name('tasks.destroy');
        Route::patch('/tasks/{task}/move', [ApiKanbanTasksController::class, 'move'])
            ->name('tasks.move');

        Route::get('/tasks/{task}/notes', [ApiKanbanTaskNotesController::class, 'index'])
            ->name('notes.index');
        Route::post('/tasks/{task}/notes', [ApiKanbanTaskNotesController::class, 'store'])
            ->name('notes.store');
        Route::patch('/notes/{note}', [ApiKanbanTaskNotesController::class, 'update'])
            ->name('notes.update');
        Route::delete('/notes/{note}', [ApiKanbanTaskNotesController::class, 'destroy'])
            ->name('notes.destroy');

        Route::get('/tasks/{task}/attachments', [ApiKanbanTaskAttachmentsController::class, 'index'])
            ->name('attachments.index');
        Route::post('/tasks/{task}/attachments', [ApiKanbanTaskAttachmentsController::class, 'store'])
            ->name('attachments.store');
        Route::get('/attachments/{attachment}', [ApiKanbanTaskAttachmentsController::class, 'show'])
            ->name('attachments.show');
        Route::delete('/attachments/{attachment}', [ApiKanbanTaskAttachmentsController::class, 'destroy'])
            ->name('attachments.destroy');
    });

Route::prefix('chat')
    ->middleware('auth')
    ->name('chat.')
    ->group(function () {
        Route::get('/sessions', [ApiAiChatSessionsController::class, 'index'])
            ->name('sessions.index');
        Route::post('/sessions', [ApiAiChatSessionsController::class, 'store'])
            ->name('sessions.store');
        Route::get('/sessions/{session}', [ApiAiChatSessionsController::class, 'show'])
            ->name('sessions.show');
        Route::patch('/sessions/{session}', [ApiAiChatSessionsController::class, 'update'])
            ->name('sessions.update');
        Route::delete('/sessions/{session}', [ApiAiChatSessionsController::class, 'destroy'])
            ->name('sessions.destroy');
        Route::post('/sessions/{session}/clear', [ApiAiChatSessionsController::class, 'clear'])
            ->name('sessions.clear');

        Route::post('/sessions/{session}/messages', [ApiAiChatMessagesController::class, 'store'])
            ->name('messages.store');
        Route::delete('/messages/{message}', [ApiAiChatMessagesController::class, 'destroy'])
            ->name('messages.destroy');

        Route::post('/attachments', [ApiAiChatAttachmentsController::class, 'store'])
            ->name('attachments.store');
        Route::get('/attachments/{attachment}', [ApiAiChatAttachmentsController::class, 'show'])
            ->name('attachments.show');
        Route::delete('/attachments/{attachment}', [ApiAiChatAttachmentsController::class, 'destroy'])
            ->name('attachments.destroy');

        Route::get('/models', [ApiAiChatModelsController::class, 'index'])
            ->name('models.index');

        Route::get('/memories', [ApiUserMemoriesController::class, 'index'])
            ->name('memories.index');
        Route::patch('/memories/{memory}', [ApiUserMemoriesController::class, 'update'])
            ->name('memories.update');
        Route::delete('/memories/{memory}', [ApiUserMemoriesController::class, 'destroy'])
            ->name('memories.destroy');

        Route::post('/realtime/token', [ApiRealtimeTokenController::class, 'store'])
            ->name('realtime.token');
        Route::post('/realtime/functions', [ApiRealtimeFunctionController::class, 'execute'])
            ->name('realtime.functions');
    });
