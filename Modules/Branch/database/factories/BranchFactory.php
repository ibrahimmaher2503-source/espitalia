<?php

namespace Modules\Branch\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Branch\Models\Branch;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition()
    {
        return [
            'name' => ['en' => $this->faker->company, 'ar' => $this->faker->company],
            'slug' => $this->faker->unique()->slug,
            'type' => $this->faker->randomElement(Branch::TYPES),
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
