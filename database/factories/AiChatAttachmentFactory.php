<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiChatAttachment;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiChatAttachment>
 */
class AiChatAttachmentFactory extends Factory
{
    protected $model = AiChatAttachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'pdf', 'txt']);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
        ];

        $uuid = Str::uuid()->toString();
        $filename = "{$uuid}.{$extension}";

        return [
            'ai_chat_message_id' => AiChatMessage::factory(),
            'ai_chat_session_id' => null,
            'filename' => $filename,
            'original_filename' => fake()->word().".{$extension}",
            'mime_type' => $mimeTypes[$extension],
            'size' => fake()->numberBetween(1024, 10485760),
            'disk' => 'chat_attachments',
            'path' => "1/1/{$filename}",
        ];
    }

    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $extension = fake()->randomElement(['jpg', 'png', 'gif', 'webp']);
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];

            $uuid = Str::uuid()->toString();

            return [
                'filename' => "{$uuid}.{$extension}",
                'original_filename' => fake()->word().".{$extension}",
                'mime_type' => $mimeTypes[$extension],
            ];
        });
    }

    public function video(): static
    {
        return $this->state(function (array $attributes) {
            $extension = fake()->randomElement(['mp4', 'webm']);
            $mimeTypes = [
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
            ];

            $uuid = Str::uuid()->toString();

            return [
                'filename' => "{$uuid}.{$extension}",
                'original_filename' => fake()->word().".{$extension}",
                'mime_type' => $mimeTypes[$extension],
            ];
        });
    }

    public function document(): static
    {
        return $this->state(function (array $attributes) {
            $extension = fake()->randomElement(['pdf', 'txt', 'md', 'json']);
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'txt' => 'text/plain',
                'md' => 'text/markdown',
                'json' => 'application/json',
            ];

            $uuid = Str::uuid()->toString();

            return [
                'filename' => "{$uuid}.{$extension}",
                'original_filename' => fake()->word().".{$extension}",
                'mime_type' => $mimeTypes[$extension],
            ];
        });
    }

    public function unattached(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'ai_chat_message_id' => null,
                'ai_chat_session_id' => AiChatSession::factory(),
            ];
        });
    }
}
