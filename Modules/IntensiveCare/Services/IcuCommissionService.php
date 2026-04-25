<?php

namespace Modules\IntensiveCare\Services;

use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;

class IcuCommissionService
{
    /**
     * Calculate commission for an accepted ICU admission request.
     * Creates an IcuCommissionEarning record with platform commission and hospital receivable.
     */
    public function calculateCommission(IcuAdmissionRequest $admissionRequest): ?IcuCommissionEarning
    {
        // Check if earning already exists for this admission (duplicate prevention)
        $existingEarning = IcuCommissionEarning::where('admission_request_id', $admissionRequest->id)->first();
        if ($existingEarning) {
            return null;
        }

        // Get the hospital's assigned commission
        $hospital = $admissionRequest->hospital;
        if (! $hospital || ! $hospital->commission_id) {
            return null;
        }

        $commission = IcuCommission::find($hospital->commission_id);
        if (! $commission) {
            return null;
        }

        // Get order subtotal from department's daily price
        $department = $admissionRequest->icuDepartment;
        $orderSubtotal = $department ? $department->daily_price : 0;

        // Handle zero subtotal case
        if ($orderSubtotal == 0) {
            return IcuCommissionEarning::create([
                'admission_request_id' => $admissionRequest->id,
                'hospital_id' => $hospital->id,
                'commission_id' => $commission->id,
                'order_subtotal' => $orderSubtotal,
                'commission_type' => $commission->commission_type,
                'commission_value' => $commission->commission_value,
                'commission_amount' => 0.00,
                'hospital_receivable' => 0.00,
                'status' => IcuCommissionEarning::STATUS_UNPAID,
            ]);
        }

        // Calculate commission amount based on type
        if ($commission->commission_type === IcuCommission::TYPE_PERCENTAGE) {
            $commissionAmount = ($orderSubtotal * $commission->commission_value) / 100;
        } else {
            // Fixed amount — cap to order subtotal to prevent negative receivable
            $commissionAmount = min($commission->commission_value, $orderSubtotal);
        }

        // Hospital receivable = subtotal - commission
        $hospitalReceivable = $orderSubtotal - $commissionAmount;

        return IcuCommissionEarning::create([
            'admission_request_id' => $admissionRequest->id,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => $orderSubtotal,
            'commission_type' => $commission->commission_type,
            'commission_value' => $commission->commission_value,
            'commission_amount' => $commissionAmount,
            'hospital_receivable' => $hospitalReceivable,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);
    }

    /**
     * Reverse/cancel commission for a cancelled admission.
     * Marks the earning record as cancelled.
     */
    public function reverseCommission(IcuAdmissionRequest $admissionRequest): bool
    {
        $earning = IcuCommissionEarning::where('admission_request_id', $admissionRequest->id)->first();
        if (! $earning) {
            return false;
        }

        // Cannot reverse a paid earning
        if ($earning->status === IcuCommissionEarning::STATUS_PAID) {
            return false;
        }

        $earning->update(['status' => IcuCommissionEarning::STATUS_CANCELLED]);

        return true;
    }
}
