<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kanban\CreateBoardAction;
use App\Actions\Kanban\DeleteBoardAction;
use App\Actions\Kanban\UpdateBoardAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\StoreBoardRequest;
use App\Http\Requests\Kanban\UpdateBoardRequest;
use App\Models\KanbanBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiKanbanBoardsController extends Controller
{
    public function index(): JsonResponse
    {
        $boards = auth()->user()->kanbanBoards()
            ->with(['columns.tasks'])
            ->get();

        return response()->json(['boards' => $boards]);
    }

    public function store(StoreBoardRequest $request, CreateBoardAction $action): JsonResponse
    {
        $board = $action->handle(
            user: auth()->user(),
            name: $request->input('name'),
            description: $request->input('description'),
        );

        return response()->json(['board' => $board], Response::HTTP_CREATED);
    }

    public function show(KanbanBoard $board): JsonResponse
    {
        $this->authorize('view', $board);

        $board->load(['columns.tasks']);

        return response()->json(['board' => $board]);
    }

    public function update(UpdateBoardRequest $request, KanbanBoard $board, UpdateBoardAction $action): JsonResponse
    {
        $this->authorize('update', $board);

        $board = $action->handle($board, $request->validated());

        return response()->json(['board' => $board]);
    }

    public function destroy(KanbanBoard $board, DeleteBoardAction $action): Response
    {
        $this->authorize('delete', $board);

        $action->handle($board);

        return response()->noContent();
    }
}
