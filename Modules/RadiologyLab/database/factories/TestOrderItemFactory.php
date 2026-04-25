<?php

namespace Modules\RadiologyLab\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\LabTest;

class TestOrderItemFactory extends Factory
{
    protected $model = TestOrderItem::class;

    public function definition()
    {
        return [
            'test_order_id' => TestOrder::factory(),
            'lab_test_id' => LabTest::factory(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'status' => TestOrderItem::STATUS_PENDING,
            'result_value' => null,
            'result_unit' => null,
            'reference_range' => null,
            'result_status' => null,
            'result_notes' => null,
            'result_date' => null,
            'result_by' => null,
        ];
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrderItem::STATUS_PENDING,
        ]);
    }

    public function sampleCollected()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrderItem::STATUS_SAMPLE_COLLECTED,
        ]);
    }

    public function processing()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrderItem::STATUS_PROCESSING,
        ]);
    }

    public function completed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrderItem::STATUS_COMPLETED,
        ]);
    }

    public function withResults()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestOrderItem::STATUS_COMPLETED,
            'result_value' => $this->faker->randomElement(['120', '5.4', 'Positive', 'Normal', 'Abnormal']),
            'result_unit' => $this->faker->randomElement(['mg/dL', 'g/L', 'U/L', 'mmol/L']),
            'reference_range' => $this->faker->randomElement(['70-120', '3.5-5.5', 'Negative', '0-10']),
            'result_status' => $this->faker->randomElement(['normal', 'abnormal', 'critical']),
            'result_notes' => $this->faker->optional()->sentence(),
            'result_date' => $this->faker->dateTime(),
            'result_by' => User::factory(),
        ]);
    }

    public function normal()
    {
        return $this->withResults()->state(fn (array $attributes) => [
            'result_status' => 'normal',
        ]);
    }

    public function abnormal()
    {
        return $this->withResults()->state(fn (array $attributes) => [
            'result_status' => 'abnormal',
        ]);
    }

    public function critical()
    {
        return $this->withResults()->state(fn (array $attributes) => [
            'result_status' => 'critical',
        ]);
    }
}
