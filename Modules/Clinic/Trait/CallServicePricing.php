<?php

namespace Modules\Clinic\Trait;

use Modules\Clinic\Models\DoctorCallService;

trait CallServicePricing
{
    protected function getCallServiceAmount($service_id)
    {
        $service = DoctorCallService::find($service_id);

        if (!$service) {
            return [
                'service_price' => 0, 'service_amount' => 0, 'total_amount' => 0,
                'duration' => 0, 'discount_type' => null, 'discount_value' => 0,
                'discount_amount' => 0, 'service_name' => null, 'call_type' => null,
                'inclusive_tax_data' => [], 'service_inclusive_tax' => 0,
            ];
        }

        $service_price = $service->charges;
        $service_amount = $service->charges;
        $discount_amount = 0;

        if ($service->discount == 1) {
            if ($service->discount_type == 'percentage') {
                $discount_amount = $service_amount * $service->discount_value / 100;
            } else {
                $discount_amount = $service->discount_value;
            }
            $service_amount = $service_amount - $discount_amount;
        }

        $inclusive_tax_data = [];
        $service_inclusive_tax = 0;
        if ($service->is_inclusive_tax == 1 && $service->inclusive_tax_price > 0) {
            $service_inclusive_tax = $service->inclusive_tax_price;
        }

        $tax_amount = 0;
        if ($service_amount > 0 && method_exists($this, 'TaxCalculation')) {
            $tax_amount = $this->TaxCalculation($service_amount);
        }

        $total_amount = $service_amount + $tax_amount + $service_inclusive_tax;

        return [
            'service_price' => $service_price,
            'service_amount' => round($service_amount, 2),
            'total_amount' => round($total_amount, 2),
            'duration' => $service->duration_min,
            'discount_type' => $service->discount_type,
            'discount_value' => $service->discount_value,
            'discount_amount' => round($discount_amount, 2),
            'service_name' => $service->name,
            'call_type' => $service->call_type,
            'inclusive_tax_data' => $inclusive_tax_data,
            'service_inclusive_tax' => $service_inclusive_tax,
        ];
    }
}
