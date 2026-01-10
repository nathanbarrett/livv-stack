<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiChatSession>
 */
class AiChatSessionFactory extends Factory
{
    protected $model = AiChatSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->optional()->words(3, true),
            'model' => fake()->randomElement([
                'claude-opus-4-5-20251101',
                'gpt-4o',
                'gemini-2.5-pro',
            ]),
            'settings' => null,
        ];
    }

    public function withTitle(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
        ]);
    }

    public function withModel(string $model): static
    {
        return $this->state(fn (array $attributes) => [
            'model' => $model,
        ]);
    }
}
