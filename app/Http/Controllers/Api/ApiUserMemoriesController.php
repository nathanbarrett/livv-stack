<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\UserMemory\DeleteMemoryAction;
use App\Actions\UserMemory\SaveMemoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserMemory\UpdateMemoryRequest;
use App\Models\UserMemory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiUserMemoriesController extends Controller
{
    public function index(): JsonResponse
    {
        $memories = auth()->user()->memories()
            ->orderBy('type')
            ->orderBy('key')
            ->get();

        return response()->json(['memories' => $memories]);
    }

    public function update(
        UpdateMemoryRequest $request,
        UserMemory $memory,
        SaveMemoryAction $action
    ): JsonResponse {
        if ($memory->user_id !== auth()->id()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $action->handle(
            user: auth()->user(),
            type: $memory->type,
            key: $memory->key,
            value: $request->input('value'),
        );

        $memory->refresh();

        return response()->json(['memory' => $memory]);
    }

    public function destroy(UserMemory $memory, DeleteMemoryAction $action): Response
    {
        if ($memory->user_id !== auth()->id()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $action->handle(auth()->user(), $memory->type, $memory->key);

        return response()->noContent();
    }
}
