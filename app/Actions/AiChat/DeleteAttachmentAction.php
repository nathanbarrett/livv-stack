<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatAttachment;
use Illuminate\Support\Facades\Storage;

class DeleteAttachmentAction
{
    public function handle(AiChatAttachment $attachment): bool
    {
        Storage::disk($attachment->disk)->delete($attachment->path);

        return $attachment->delete();
    }
}
