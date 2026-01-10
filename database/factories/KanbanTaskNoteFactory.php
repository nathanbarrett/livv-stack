<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KanbanTask;
use App\Models\KanbanTaskNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KanbanTaskNote>
 */
class KanbanTaskNoteFactory extends Factory
{
    protected $model = KanbanTaskNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kanban_task_id' => KanbanTask::factory(),
            'note' => fake()->paragraph(),
            'author' => fake()->randomElement(['user', 'ai']),
        ];
    }

    public function byUser(): static
    {
        return $this->state(fn () => ['author' => 'user']);
    }

    public function byAi(): static
    {
        return $this->state(fn () => ['author' => 'ai']);
    }
}
