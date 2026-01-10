<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiChatMessageRole;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiChatMessage>
 */
class AiChatMessageFactory extends Factory
{
    protected $model = AiChatMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_chat_session_id' => AiChatSession::factory(),
            'role' => fake()->randomElement(AiChatMessageRole::cases()),
            'content' => fake()->paragraph(),
            'model' => null,
            'usage' => null,
            'metadata' => null,
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AiChatMessageRole::User,
            'model' => null,
            'usage' => null,
        ]);
    }

    public function assistant(?string $model = 'claude-opus-4-5-20251101'): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AiChatMessageRole::Assistant,
            'model' => $model,
            'usage' => [
                'prompt_tokens' => fake()->numberBetween(100, 1000),
                'completion_tokens' => fake()->numberBetween(50, 500),
            ],
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AiChatMessageRole::System,
            'model' => null,
            'usage' => null,
        ]);
    }
}
