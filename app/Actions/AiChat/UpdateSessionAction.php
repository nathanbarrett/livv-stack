<?php

declare(strict_types=1);

namespace App\Actions\AiChat;

use App\Models\AiChatSession;

class UpdateSessionAction
{
    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function handle(
        AiChatSession $session,
        ?string $title = null,
        ?string $model = null,
        ?array $settings = null
    ): AiChatSession {
        $session->update(array_filter([
            'title' => $title,
            'model' => $model,
            'settings' => $settings,
        ], fn ($value) => $value !== null));

        return $session->fresh();
    }
}
