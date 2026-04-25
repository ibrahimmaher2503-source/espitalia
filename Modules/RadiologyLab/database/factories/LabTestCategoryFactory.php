<?php

namespace Modules\RadiologyLab\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\LabTestCategory;

class LabTestCategoryFactory extends Factory
{
    protected $model = LabTestCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => null, // Will be auto-generated
            'description' => $this->faker->paragraph(),
            'icon' => $this->faker->randomElement(['ph-flask', 'ph-x-ray', 'ph-microscope', 'ph-test-tube']),
            'display_order' => $this->faker->numberBetween(1, 100),
            'status' => 1,
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }
}
