<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserMemory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserMemory>
 */
class UserMemoryFactory extends Factory
{
    protected $model = UserMemory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'personal',
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
        ];
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
        ]);
    }

    public function withType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    public function withKey(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
        ]);
    }

    public function withValue(string $value): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value,
        ]);
    }
}
