<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KanbanTask>
 */
class KanbanTaskFactory extends Factory
{
    protected $model = KanbanTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kanban_column_id' => KanbanColumn::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'position' => 0,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'priority' => fake()->optional()->randomElement(['low', 'medium', 'high']),
        ];
    }
}
