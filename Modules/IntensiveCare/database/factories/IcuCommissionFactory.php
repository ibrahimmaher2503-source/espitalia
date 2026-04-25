<?php

namespace Modules\IntensiveCare\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\IntensiveCare\Models\IcuCommission;

class IcuCommissionFactory extends Factory
{
    protected $model = IcuCommission::class;

    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => $this->faker->numberBetween(5, 25),
            'status' => true,
        ];
    }

    public function percentage()
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
        ]);
    }

    public function fixed()
    {
        return $this->state(fn (array $attributes) => [
            'commission_type' => IcuCommission::TYPE_FIXED,
        ]);
    }

    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
