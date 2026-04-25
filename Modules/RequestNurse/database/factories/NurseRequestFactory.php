<?php

namespace Modules\RequestNurse\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseRequestFactory extends Factory
{
    protected $model = \Modules\RequestNurse\Models\NurseRequest::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'nurse_id' => null,
            'service_description_ar' => $this->faker->paragraph,
            'service_description_en' => $this->faker->paragraphs(2, true),
            'request_date' => now(),
            'preferred_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'preferred_time' => $this->faker->time('H:i'),
            'duration_hours' => $this->faker->numberBetween(1, 8),
            'address_line_1' => $this->faker->streetAddress,
            'address_line_2' => $this->faker->secondaryAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->country,
            'postal_code' => $this->faker->postcode,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'contact_number' => $this->faker->phoneNumber,
            'status' => 'pending',
            'payment_status' => false,
            'total_amount' => 0,
            'patient_notes' => $this->faker->sentence,
            'admin_notes' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ];
    }
}
