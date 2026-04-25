<?php

namespace Modules\RadiologyLab\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\RadiologyLab\Models\TestReport;

class TestReportFactory extends Factory
{
    protected $model = TestReport::class;

    public function definition()
    {
        return [
            'test_order_id' => null, // Will be set in tests
            'report_type' => $this->faker->randomElement([
                TestReport::TYPE_LAB_REPORT,
                TestReport::TYPE_RADIOLOGY_REPORT,
                TestReport::TYPE_CUSTOM,
            ]),
            'title' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'generated_by' => User::factory(),
            'report_date' => $this->faker->date(),
            'status' => TestReport::STATUS_DRAFT,
        ];
    }

    public function draft()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestReport::STATUS_DRAFT,
        ]);
    }

    public function final()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestReport::STATUS_FINAL,
        ]);
    }

    public function amended()
    {
        return $this->state(fn (array $attributes) => [
            'status' => TestReport::STATUS_AMENDED,
        ]);
    }

    public function labReport()
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => TestReport::TYPE_LAB_REPORT,
        ]);
    }

    public function radiologyReport()
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => TestReport::TYPE_RADIOLOGY_REPORT,
        ]);
    }

    public function custom()
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => TestReport::TYPE_CUSTOM,
        ]);
    }
}
