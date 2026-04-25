<?php

namespace Modules\IntensiveCare\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuDepartment;

class IcuDepartmentFactory extends Factory
{
    protected $model = IcuDepartment::class;

    public function definition()
    {
        return [
            'hospital_id' => Hospital::factory(),
            'name_en' => $this->faker->words(2, true),
            'name_ar' => $this->faker->words(2, true),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'specialty_type' => $this->faker->randomElement(IcuDepartment::SPECIALTY_TYPES),
            'total_beds' => $totalBeds = $this->faker->numberBetween(5, 30),
            'available_beds' => $this->faker->numberBetween(0, $totalBeds),
            'has_ventilator' => $this->faker->boolean(),
            'has_oxygen' => true,
            'equipment_level' => $this->faker->randomElement(IcuDepartment::EQUIPMENT_LEVELS),
            'daily_price' => $this->faker->randomFloat(2, 100, 5000),
            'status' => 1,
        ];
    }

    public function cardiac()
    {
        return $this->state(fn (array $attributes) => [
            'specialty_type' => 'cardiac',
        ]);
    }

    public function neurology()
    {
        return $this->state(fn (array $attributes) => [
            'specialty_type' => 'neurology',
        ]);
    }

    public function available()
    {
        return $this->state(fn (array $attributes) => [
            'available_beds' => $this->faker->numberBetween(1, 15),
        ]);
    }

    public function full()
    {
        return $this->state(fn (array $attributes) => [
            'available_beds' => 0,
        ]);
    }

    public function withVentilator()
    {
        return $this->state(fn (array $attributes) => [
            'has_ventilator' => true,
        ]);
    }
}
