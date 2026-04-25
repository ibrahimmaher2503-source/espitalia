<?php

namespace Modules\RadiologyLab\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;

class LabTestFactory extends Factory
{
    protected $model = LabTest::class;

    public function definition()
    {
        return [
            'category_id' => LabTestCategory::factory(),
            'name' => $this->faker->words(3, true),
            'slug' => null, // Will be auto-generated
            'code' => strtoupper($this->faker->unique()->bothify('LAB-###')),
            'description' => $this->faker->paragraph(),
            'sample_type' => $this->faker->randomElement(LabTest::SAMPLE_TYPES),
            'department' => $this->faker->randomElement(LabTest::DEPARTMENTS),
            'preparation_instructions' => $this->faker->sentence(),
            'default_price' => $this->faker->randomFloat(2, 10, 500),
            'turnaround_time' => $this->faker->randomElement(['1-2 days', '3-5 days', '1 week']),
            'status' => 1,
        ];
    }

    public function laboratory()
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'laboratory',
        ]);
    }

    public function radiology()
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'radiology',
        ]);
    }

    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
