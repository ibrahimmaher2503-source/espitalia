<?php

namespace Modules\Clinic\Trait;

use Modules\Clinic\Models\DoctorIndependentService;

trait IndependentServicePricing
{
    protected function getIndependentServiceAmount($service_id)
    {
        $service = DoctorIndependentService::find($service_id);

        if (!$service) {
            return [
                'service_price' => 0, 'service_amount' => 0, 'total_amount' => 0,
                'duration' => 0, 'discount_type' => null, 'discount_value' => 0,
                'discount_amount' => 0, 'service_name' => null,
                'inclusive_tax_data' => [], 'service_inclusive_tax' => 0,
            ];
        }

        $service_price = $service->charges;
        $service_amount = $service->charges;
        $discount_amount = 0;

        // Apply discount
        if ($service->discount == 1) {
            if ($service->discount_type == 'percentage') {
                $discount_amount = $service_amount * $service->discount_value / 100;
            } else {
                $discount_amount = $service->discount_value;
            }
            $service_amount = $service_amount - $discount_amount;
        }

        // Inclusive tax
        $inclusive_tax_data = [];
        $service_inclusive_tax = 0;
        if ($service->is_inclusive_tax == 1 && $service->inclusive_tax_price > 0) {
            $service_inclusive_tax = $service->inclusive_tax_price;
        }

        // Exclusive tax (uses TaxCalculation from AppointmentTrait if available)
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
            'inclusive_tax_data' => $inclusive_tax_data,
            'service_inclusive_tax' => $service_inclusive_tax,
        ];
    }
}
