<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'due_at' => fake()->optional(0.8)->dateTimeBetween('now', '+30 days'),
            'created_by' => User::factory(),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Todo]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::InProgress]);
    }

    public function onHold(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::OnHold]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Cancelled]);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => TaskPriority::Urgent]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Todo,
            'due_at' => fake()->dateTimeBetween('-7 days', '-1 day'),
        ]);
    }
}
