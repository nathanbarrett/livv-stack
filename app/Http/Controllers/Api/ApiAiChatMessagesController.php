<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AiChat\SendMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiChat\StoreMessageRequest;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiAiChatMessagesController extends Controller
{
    public function store(
        StoreMessageRequest $request,
        AiChatSession $session,
        SendMessageAction $action
    ): JsonResponse {
        $this->authorize('sendMessage', $session);

        $result = $action->handle(
            session: $session,
            content: $request->input('content'),
            attachmentIds: $request->input('attachment_ids', []),
        );

        return response()->json([
            'user_message' => $result['user_message'],
            'assistant_message' => $result['assistant_message'],
        ], Response::HTTP_CREATED);
    }

    public function destroy(AiChatMessage $message): Response
    {
        $session = $message->session;

        $this->authorize('update', $session);

        $message->delete();

        return response()->noContent();
    }
}
