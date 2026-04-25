<?php

namespace Modules\IntensiveCare\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\IntensiveCare\Models\Hospital;

class HospitalFactory extends Factory
{
    protected $model = Hospital::class;

    public function definition()
    {
        $nameEn = $this->faker->words(2, true);

        return [
            'name_ar' => $this->faker->words(2, true),
            'name_en' => $nameEn,
            'slug' => Str::slug($nameEn) . '-' . $this->faker->unique()->randomNumber(5),
            'description_ar' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'hospital_type' => $this->faker->randomElement(Hospital::HOSPITAL_TYPES),
            'address_ar' => $this->faker->address(),
            'address_en' => $this->faker->address(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'accepted_insurance' => $this->faker->randomElements(
                ['Insurance A', 'Insurance B', 'Insurance C', 'National Insurance'],
                2
            ),
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'status' => 1,
        ];
    }

    public function government()
    {
        return $this->state(fn (array $attributes) => [
            'hospital_type' => 'government',
        ]);
    }

    public function private()
    {
        return $this->state(fn (array $attributes) => [
            'hospital_type' => 'private',
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
