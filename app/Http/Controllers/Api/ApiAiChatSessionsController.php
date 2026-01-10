<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AiChat\ClearSessionAction;
use App\Actions\AiChat\CreateSessionAction;
use App\Actions\AiChat\DeleteSessionAction;
use App\Actions\AiChat\UpdateSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiChat\StoreSessionRequest;
use App\Http\Requests\AiChat\UpdateSessionRequest;
use App\Models\AiChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiAiChatSessionsController extends Controller
{
    public function index(): JsonResponse
    {
        $sessions = auth()->user()->chatSessions()
            ->with('latestMessage')
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    public function store(StoreSessionRequest $request, CreateSessionAction $action): JsonResponse
    {
        $session = $action->handle(
            user: auth()->user(),
            title: $request->input('title'),
            model: $request->input('model'),
            settings: $request->input('settings'),
        );

        return response()->json(['session' => $session], Response::HTTP_CREATED);
    }

    public function show(AiChatSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $session->load(['messages.attachments']);

        return response()->json(['session' => $session]);
    }

    public function update(
        UpdateSessionRequest $request,
        AiChatSession $session,
        UpdateSessionAction $action
    ): JsonResponse {
        $this->authorize('update', $session);

        $session = $action->handle(
            session: $session,
            title: $request->input('title'),
            model: $request->input('model'),
            settings: $request->input('settings'),
        );

        return response()->json(['session' => $session]);
    }

    public function destroy(AiChatSession $session, DeleteSessionAction $action): Response
    {
        $this->authorize('delete', $session);

        $action->handle($session);

        return response()->noContent();
    }

    public function clear(AiChatSession $session, ClearSessionAction $action): JsonResponse
    {
        $this->authorize('clear', $session);

        $session = $action->handle($session);

        return response()->json(['session' => $session]);
    }
}
