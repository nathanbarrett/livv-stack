<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTask;
use App\Models\KanbanTaskAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadTaskAttachmentAction
{
    public function handle(KanbanTask $task, UploadedFile $file): KanbanTaskAttachment
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$extension}";

        $board = $task->getBoard();
        $path = "{$board->user_id}/{$board->id}/{$task->id}/{$filename}";

        Storage::disk('kanban_attachments')->put($path, $file->getContent());

        return KanbanTaskAttachment::create([
            'kanban_task_id' => $task->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'disk' => 'kanban_attachments',
            'path' => $path,
        ]);
    }
}
