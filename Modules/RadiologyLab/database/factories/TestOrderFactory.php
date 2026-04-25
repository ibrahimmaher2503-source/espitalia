<?php

namespace Modules\RadiologyLab\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\TestOrder;

class TestOrderFactory extends Factory
{
    protected $model = TestOrder::class;

    public function definition()
    {
        return [
            'order_number' => null, // Will be auto-generated
            'patient_id' => User::factory(),
            'doctor_id' => User::factory(),
            'lab_technician_id' => null,
            'clinical_notes' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(TestOrder::PRIORITIES),
            'order_date' => $this->faker->date(),
            'status' => TestOrder::STATUS_PENDING,
            'payment_status' => $this->faker->randomElement(['paid', 'unpaid', 'refunded']),
            'total_amount' => $this->faker->randomFloat(2, 50, 1000),
            'discount_amount' => 0,
            'final_amount' => function (array $attributes) {
                return $attributes['total_amount'] - $attributes['discount_amount'];
            },
            'admin_notes' => $this->faker->optional()->sentence(),
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ];
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_PENDING,
        ]);
    }

    public function confirmed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_CONFIRMED,
        ]);
    }

    public function sampleCollected()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_SAMPLE_COLLECTED,
        ]);
    }

    public function processing()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_PROCESSING,
        ]);
    }

    public function completed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_COMPLETED,
        ]);
    }

    public function delivered()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_DELIVERED,
        ]);
    }

    public function cancelled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrder::STATUS_CANCELLED,
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }

    public function urgent()
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TestOrder::PRIORITY_URGENT,
        ]);
    }

    public function stat()
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TestOrder::PRIORITY_STAT,
        ]);
    }

    public function routine()
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TestOrder::PRIORITY_ROUTINE,
        ]);
    }

    public function withTechnician()
    {
        return $this->state(fn (array $attributes) => [
            'lab_technician_id' => User::factory(),
        ]);
    }
}
