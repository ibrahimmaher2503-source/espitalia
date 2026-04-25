<?php

namespace Modules\IntensiveCare\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;

class IcuAdmissionRequestFactory extends Factory
{
    protected $model = IcuAdmissionRequest::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory(),
            'hospital_id' => Hospital::factory(),
            'icu_department_id' => null,
            'patient_name' => $this->faker->name(),
            'patient_age' => $this->faker->numberBetween(1, 90),
            'patient_gender' => $this->faker->randomElement(IcuAdmissionRequest::GENDER_OPTIONS),
            'medical_condition_en' => $this->faker->paragraph(),
            'medical_condition_ar' => $this->faker->paragraph(),
            'case_type' => $this->faker->randomElement(IcuAdmissionRequest::CASE_TYPES),
            'urgency' => 'standard',
            'needs_ventilator' => false,
            'needs_oxygen' => false,
            'needs_ambulance' => false,
            'contact_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'payment_method' => 'cash',
            'status' => 'pending',
        ];
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuAdmissionRequest::STATUS_PENDING,
        ]);
    }

    public function accepted()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuAdmissionRequest::STATUS_ACCEPTED,
        ]);
    }

    public function rejected()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuAdmissionRequest::STATUS_REJECTED,
        ]);
    }

    public function infoRequested()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuAdmissionRequest::STATUS_INFO_REQUESTED,
        ]);
    }

    public function cancelled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuAdmissionRequest::STATUS_CANCELLED,
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }

    public function critical()
    {
        return $this->state(fn (array $attributes) => [
            'urgency' => 'critical',
        ]);
    }

    public function urgent()
    {
        return $this->state(fn (array $attributes) => [
            'urgency' => 'urgent',
        ]);
    }
}
