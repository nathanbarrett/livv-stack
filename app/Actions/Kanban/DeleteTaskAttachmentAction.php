<?php

declare(strict_types=1);

namespace App\Actions\Kanban;

use App\Models\KanbanTaskAttachment;
use Illuminate\Support\Facades\Storage;

class DeleteTaskAttachmentAction
{
    public function handle(KanbanTaskAttachment $attachment): bool
    {
        Storage::disk($attachment->disk)->delete($attachment->path);

        return $attachment->delete();
    }
}
