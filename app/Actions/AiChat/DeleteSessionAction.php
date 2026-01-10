<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatSession;
use Illuminate\Support\Facades\Storage;

class DeleteSessionAction
{
    public function handle(AiChatSession $session): bool
    {
        $userId = $session->user_id;
        $sessionId = $session->id;

        $deleted = $session->delete();

        if ($deleted) {
            Storage::disk('chat_attachments')->deleteDirectory("{$userId}/{$sessionId}");
        }

        return $deleted;
    }
}
