<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ApiKanbanBoardsController;
use App\Http\Controllers\Api\ApiKanbanColumnsController;
use App\Http\Controllers\Api\ApiKanbanTasksController;
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
    });
