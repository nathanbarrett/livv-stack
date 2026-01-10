<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\AiChat\DeleteAttachmentAction;
use App\Actions\AiChat\UploadAttachmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiChat\StoreAttachmentRequest;
use App\Models\AiChatAttachment;
use App\Models\AiChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ApiAiChatAttachmentsController extends Controller
{
    public function store(StoreAttachmentRequest $request, UploadAttachmentAction $action): JsonResponse
    {
        $session = AiChatSession::findOrFail($request->input('session_id'));

        $this->authorize('update', $session);

        $attachment = $action->handle(
            session: $session,
            file: $request->file('file'),
        );

        return response()->json([
            'attachment' => [
                'id' => $attachment->id,
                'original_filename' => $attachment->original_filename,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(AiChatAttachment $attachment): JsonResponse
    {
        $session = $attachment->session ?? $attachment->message?->session;

        if (! $session) {
            return response()->json(['error' => 'Attachment not found'], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('view', $session);

        $url = Storage::disk($attachment->disk)->temporaryUrl(
            $attachment->path,
            now()->addMinutes(30)
        );

        return response()->json([
            'attachment' => [
                'id' => $attachment->id,
                'original_filename' => $attachment->original_filename,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'url' => $url,
            ],
        ]);
    }

    public function destroy(AiChatAttachment $attachment, DeleteAttachmentAction $action): Response
    {
        $session = $attachment->session ?? $attachment->message?->session;

        if ($session) {
            $this->authorize('update', $session);
        } else {
            abort(Response::HTTP_FORBIDDEN);
        }

        $action->handle($attachment);

        return response()->noContent();
    }
}
