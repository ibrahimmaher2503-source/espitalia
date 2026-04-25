<?php

namespace Modules\Pharmacy\Services;

use Modules\Pharmacy\Models\PharmacyCommission;
use Modules\Pharmacy\Models\PharmacyCommissionEarning;
use Modules\Pharmacy\Models\PharmacyOrder;

class PharmacyCommissionService
{
    /**
     * Calculate commission for a delivered pharmacy order.
     * Creates a PharmacyCommissionEarning record with platform commission and pharmacy receivable.
     */
    public function calculateCommission(PharmacyOrder $order): ?PharmacyCommissionEarning
    {
        // Check if earning already exists for this order (duplicate prevention)
        $existingEarning = PharmacyCommissionEarning::where('order_id', $order->id)->first();
        if ($existingEarning) {
            return null;
        }

        // Get the pharmacy's assigned commission
        $pharmacy = $order->pharmacy;
        if (! $pharmacy || ! $pharmacy->commission_id) {
            // No commission assigned to this pharmacy, skip
            return null;
        }

        $commission = PharmacyCommission::find($pharmacy->commission_id);
        if (! $commission) {
            return null;
        }

        // Commission base = subtotal minus coupon discount (net product revenue)
        // Delivery fee is excluded as it's a separate cost center
        // Floor at 0 to prevent negative commission if coupon_discount > subtotal
        $orderSubtotal = max(0, $order->subtotal - ($order->coupon_discount ?? 0));

        // Handle zero subtotal case
        if ($orderSubtotal == 0) {
            return PharmacyCommissionEarning::create([
                'order_id' => $order->id,
                'pharmacy_id' => $pharmacy->id,
                'commission_id' => $commission->id,
                'order_subtotal' => $orderSubtotal,
                'commission_type' => $commission->commission_type,
                'commission_value' => $commission->commission_value,
                'commission_amount' => 0.00,
                'pharmacy_receivable' => 0.00,
                'status' => PharmacyCommissionEarning::STATUS_UNPAID,
            ]);
        }

        // Calculate commission amount based on type
        if ($commission->commission_type === PharmacyCommission::TYPE_PERCENTAGE) {
            $commissionAmount = ($orderSubtotal * $commission->commission_value) / 100;
        } else {
            // Fixed amount — cap to order subtotal to prevent negative receivable
            $commissionAmount = min($commission->commission_value, $orderSubtotal);
        }

        // Pharmacy receivable = subtotal - commission
        $pharmacyReceivable = $orderSubtotal - $commissionAmount;

        // Create earning record
        return PharmacyCommissionEarning::create([
            'order_id' => $order->id,
            'pharmacy_id' => $pharmacy->id,
            'commission_id' => $commission->id,
            'order_subtotal' => $orderSubtotal,
            'commission_type' => $commission->commission_type,
            'commission_value' => $commission->commission_value,
            'commission_amount' => $commissionAmount,
            'pharmacy_receivable' => $pharmacyReceivable,
            'status' => PharmacyCommissionEarning::STATUS_UNPAID,
        ]);
    }

    /**
     * Reverse/cancel commission for a cancelled order.
     * Marks the earning record as cancelled.
     */
    public function reverseCommission(PharmacyOrder $order): bool
    {
        $earning = PharmacyCommissionEarning::where('order_id', $order->id)->first();
        if (! $earning) {
            return false;
        }

        // Cannot reverse a paid earning — would need manual refund adjustment
        if ($earning->status === PharmacyCommissionEarning::STATUS_PAID) {
            return false;
        }

        $earning->update(['status' => PharmacyCommissionEarning::STATUS_CANCELLED]);

        return true;
    }
}
