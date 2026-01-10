<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatSession;
use App\Models\User;

class CreateSessionAction
{
    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function handle(
        User $user,
        ?string $title = null,
        ?string $model = null,
        ?array $settings = null
    ): AiChatSession {
        return $user->chatSessions()->create([
            'title' => $title,
            'model' => $model,
            'settings' => $settings,
        ]);
    }
}
