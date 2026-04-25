<?php

namespace Modules\RequestNurse\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseFactory extends Factory
{
    protected $model = \Modules\RequestNurse\Models\Nurse::class;

    public function definition()
    {
        return [
            'nurse_id' => User::factory(),
            'vendor_id' => null,
            'specialization_ar' => $this->faker->randomElement(['أطفال', 'مسنين', 'قلب', 'عظام', 'عام']),
            'specialization_en' => $this->faker->randomElement(['Pediatric', 'Geriatric', 'Cardiac', 'Orthopedic', 'General']),
            'experience_ar' => $this->faker->randomElement(['سنة واحدة', 'سنتان', '3 سنوات', '5 سنوات', '10 سنوات']),
            'experience_en' => $this->faker->randomElement(['1 year', '2 years', '3 years', '5 years', '10 years']),
            'about_ar' => $this->faker->paragraph,
            'about_en' => $this->faker->paragraphs(3, true),
            'availability_status' => $this->faker->randomElement(['available', 'busy', 'off_duty']),
            'service_area_ar' => $this->faker->city,
            'service_area_en' => $this->faker->city,
            'hourly_rate' => $this->faker->randomFloat(2, 10, 200),
            'status' => 1,
        ];
    }
}
