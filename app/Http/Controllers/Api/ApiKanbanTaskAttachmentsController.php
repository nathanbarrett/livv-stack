<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Kanban\DeleteTaskAttachmentAction;
use App\Actions\Kanban\UploadTaskAttachmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\StoreTaskAttachmentRequest;
use App\Models\KanbanTask;
use App\Models\KanbanTaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ApiKanbanTaskAttachmentsController extends Controller
{
    public function index(KanbanTask $task): JsonResponse
    {
        $this->authorize('view', $task->column->board);

        return response()->json([
            'attachments' => $task->attachments,
        ]);
    }

    public function store(
        StoreTaskAttachmentRequest $request,
        KanbanTask $task,
        UploadTaskAttachmentAction $action,
    ): JsonResponse {
        $this->authorize('update', $task->column->board);

        if ($task->attachments()->count() >= 10) {
            return response()->json([
                'message' => 'Maximum 10 attachments per task',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $attachment = $action->handle($task, $request->file('file'));

        return response()->json([
            'attachment' => [
                'id' => $attachment->id,
                'original_filename' => $attachment->original_filename,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(KanbanTaskAttachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment->task->column->board);

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

    public function destroy(
        KanbanTaskAttachment $attachment,
        DeleteTaskAttachmentAction $action,
    ): Response {
        $this->authorize('update', $attachment->task->column->board);

        $action->handle($attachment);

        return response()->noContent();
    }
}
