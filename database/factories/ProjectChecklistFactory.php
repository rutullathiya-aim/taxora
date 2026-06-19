<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectChecklist>
 */
class ProjectChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->boolean() ? fake()->sentence() : null,
            'is_mandatory' => true,
            'status' => 'Pending',
        ];
    }
}
