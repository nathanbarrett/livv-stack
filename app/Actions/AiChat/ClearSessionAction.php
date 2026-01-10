<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatSession;
use Illuminate\Support\Facades\Storage;

class ClearSessionAction
{
    public function handle(AiChatSession $session): AiChatSession
    {
        $userId = $session->user_id;
        $sessionId = $session->id;

        $session->messages()->delete();

        Storage::disk('chat_attachments')->deleteDirectory("{$userId}/{$sessionId}");

        $session->update(['title' => null]);

        return $session->fresh();
    }
}
