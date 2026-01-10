<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kanban\CreateTaskNoteAction;
use App\Actions\Kanban\DeleteTaskNoteAction;
use App\Actions\Kanban\UpdateTaskNoteAction;
use App\Enums\KanbanTaskNoteAuthor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\StoreTaskNoteRequest;
use App\Http\Requests\Kanban\UpdateTaskNoteRequest;
use App\Models\KanbanTask;
use App\Models\KanbanTaskNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiKanbanTaskNotesController extends Controller
{
    public function index(KanbanTask $task): JsonResponse
    {
        $this->authorize('view', $task->column->board);

        return response()->json([
            'notes' => $task->notes()->orderBy('created_at')->get(),
        ]);
    }

    public function store(
        StoreTaskNoteRequest $request,
        KanbanTask $task,
        CreateTaskNoteAction $action,
    ): JsonResponse {
        $this->authorize('update', $task->column->board);

        $note = $action->handle(
            task: $task,
            note: $request->input('note'),
            author: KanbanTaskNoteAuthor::from($request->input('author')),
        );

        return response()->json(['note' => $note], Response::HTTP_CREATED);
    }

    public function update(
        UpdateTaskNoteRequest $request,
        KanbanTaskNote $note,
        UpdateTaskNoteAction $action,
    ): JsonResponse {
        $this->authorize('update', $note->task->column->board);

        $note = $action->handle($note, $request->input('note'));

        return response()->json(['note' => $note]);
    }

    public function destroy(KanbanTaskNote $note, DeleteTaskNoteAction $action): Response
    {
        $this->authorize('update', $note->task->column->board);

        $action->handle($note);

        return response()->noContent();
    }
}
