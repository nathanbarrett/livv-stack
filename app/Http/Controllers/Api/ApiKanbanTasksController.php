<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kanban\CreateTaskAction;
use App\Actions\Kanban\DeleteTaskAction;
use App\Actions\Kanban\MoveTaskAction;
use App\Actions\Kanban\UpdateTaskAction;
use App\Enums\KanbanTaskPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\MoveTaskRequest;
use App\Http\Requests\Kanban\StoreTaskRequest;
use App\Http\Requests\Kanban\UpdateTaskRequest;
use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiKanbanTasksController extends Controller
{
    public function store(StoreTaskRequest $request, KanbanColumn $column, CreateTaskAction $action): JsonResponse
    {
        $this->authorize('update', $column->board);

        $priority = $request->input('priority')
            ? KanbanTaskPriority::from($request->input('priority'))
            : null;

        $dueDate = $request->input('due_date')
            ? Carbon::parse($request->input('due_date'))
            : null;

        $task = $action->handle(
            column: $column,
            title: $request->input('title'),
            description: $request->input('description'),
            dueDate: $dueDate,
            priority: $priority,
        );

        return response()->json(['task' => $task], Response::HTTP_CREATED);
    }

    public function update(UpdateTaskRequest $request, KanbanTask $task, UpdateTaskAction $action): JsonResponse
    {
        $this->authorize('update', $task->column->board);

        $task = $action->handle($task, $request->validated());

        return response()->json(['task' => $task]);
    }

    public function destroy(KanbanTask $task, DeleteTaskAction $action): Response
    {
        $this->authorize('update', $task->column->board);

        $action->handle($task);

        return response()->noContent();
    }

    public function move(MoveTaskRequest $request, KanbanTask $task, MoveTaskAction $action): JsonResponse
    {
        $this->authorize('update', $task->column->board);

        $targetColumn = KanbanColumn::findOrFail($request->integer('kanban_column_id'));
        $this->authorize('update', $targetColumn->board);

        $task = $action->handle(
            task: $task,
            targetColumnId: $targetColumn->id,
            newPosition: $request->integer('position'),
        );

        return response()->json(['task' => $task]);
    }
}
