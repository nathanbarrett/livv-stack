<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatAttachment;
use App\Models\AiChatSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadAttachmentAction
{
    public function handle(AiChatSession $session, UploadedFile $file): AiChatAttachment
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$extension}";

        $path = "{$session->user_id}/{$session->id}/{$filename}";

        Storage::disk('chat_attachments')->put($path, $file->getContent());

        return AiChatAttachment::create([
            'ai_chat_message_id' => null,
            'ai_chat_session_id' => $session->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'disk' => 'chat_attachments',
            'path' => $path,
        ]);
    }
}
