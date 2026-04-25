<?php

namespace Modules\IntensiveCare\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;

class IcuCommissionEarningFactory extends Factory
{
    protected $model = IcuCommissionEarning::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $commissionValue = $this->faker->randomFloat(2, 5, 20);
        $commissionAmount = round($subtotal * $commissionValue / 100, 2);
        $hospitalReceivable = round($subtotal - $commissionAmount, 2);

        return [
            'admission_request_id' => IcuAdmissionRequest::factory(),
            'hospital_id' => Hospital::factory(),
            'commission_id' => IcuCommission::factory(),
            'order_subtotal' => $subtotal,
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => $commissionValue,
            'commission_amount' => $commissionAmount,
            'hospital_receivable' => $hospitalReceivable,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ];
    }

    public function unpaid()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);
    }

    public function paid()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuCommissionEarning::STATUS_PAID,
        ]);
    }

    public function cancelled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => IcuCommissionEarning::STATUS_CANCELLED,
        ]);
    }
}
