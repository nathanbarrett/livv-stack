<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KanbanColumn>
 */
class KanbanColumnFactory extends Factory
{
    protected $model = KanbanColumn::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kanban_board_id' => KanbanBoard::factory(),
            'name' => fake()->word(),
            'position' => 0,
            'color' => fake()->optional()->hexColor(),
        ];
    }
}
