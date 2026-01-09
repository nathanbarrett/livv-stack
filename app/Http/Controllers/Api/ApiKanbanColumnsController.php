<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kanban\CreateColumnAction;
use App\Actions\Kanban\DeleteColumnAction;
use App\Actions\Kanban\MoveColumnAction;
use App\Actions\Kanban\UpdateColumnAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\MoveColumnRequest;
use App\Http\Requests\Kanban\StoreColumnRequest;
use App\Http\Requests\Kanban\UpdateColumnRequest;
use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiKanbanColumnsController extends Controller
{
    public function store(StoreColumnRequest $request, KanbanBoard $board, CreateColumnAction $action): JsonResponse
    {
        $this->authorize('update', $board);

        $column = $action->handle(
            board: $board,
            name: $request->input('name'),
            color: $request->input('color'),
        );

        return response()->json(['column' => $column], Response::HTTP_CREATED);
    }

    public function update(UpdateColumnRequest $request, KanbanColumn $column, UpdateColumnAction $action): JsonResponse
    {
        $this->authorize('update', $column->board);

        $column = $action->handle($column, $request->validated());

        return response()->json(['column' => $column]);
    }

    public function destroy(KanbanColumn $column, DeleteColumnAction $action): Response
    {
        $this->authorize('update', $column->board);

        $action->handle($column);

        return response()->noContent();
    }

    public function move(MoveColumnRequest $request, KanbanColumn $column, MoveColumnAction $action): JsonResponse
    {
        $this->authorize('update', $column->board);

        $column = $action->handle(
            column: $column,
            newPosition: $request->integer('position'),
        );

        return response()->json(['column' => $column]);
    }
}
