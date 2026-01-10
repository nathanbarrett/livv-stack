<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AiChatSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Prism\Prism\Facades\Prism;
use Throwable;

class GenerateChatTitleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public AiChatSession $session,
        public string $firstMessage
    ) {}

    public function handle(): void
    {
        $session = $this->session->fresh();

        if (! $session || $session->title !== null) {
            return;
        }

        try {
            $response = Prism::text()
                ->using('anthropic', 'claude-haiku-4-5-20251001')
                ->withPrompt("Generate a concise 3-5 word title for this conversation. Return ONLY the title, no quotes or punctuation.\n\nFirst message: {$this->firstMessage}")
                ->asText();

            $title = trim($response->text, " \t\n\r\0\x0B\"'");
            $title = substr($title, 0, 255);

            if (! empty($title)) {
                $session->update(['title' => $title]);
            }
        } catch (Throwable) {
            $session->update(['title' => 'New Chat']);
        }
    }
}
