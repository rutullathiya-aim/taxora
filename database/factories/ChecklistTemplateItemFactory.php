<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTemplateItem>
 */
class ChecklistTemplateItemFactory extends Factory
{
    protected $model = ChecklistTemplateItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => ChecklistTemplate::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['KYC Documents', 'Technical Documents', 'Financial Documents', null]),
            'is_mandatory' => true,
            'allowed_file_types' => 'pdf,jpg,png',
            'sort_order' => 0,
        ];
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => false,
        ]);
    }
}
