<?php

namespace Modules\Offer\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferUsage;

class OfferEligibilityService
{
    /**
     * Atomically record an offer usage: validates, inserts usage row,
     * and increments used_count within a transaction + row lock.
     *
     * @return array{success: bool, reason: ?string, usage: ?OfferUsage}
     */
    public function recordUsage(Offer $offer, array $context, array $bookingData): array
    {
        return DB::transaction(function () use ($offer, $context, $bookingData) {
            // Lock the offer row for update to prevent concurrent over-usage
            $lockedOffer = Offer::where('id', $offer->id)->lockForUpdate()->first();

            if (! $lockedOffer) {
                return ['success' => false, 'reason' => 'offer_not_found', 'usage' => null];
            }

            // Re-validate with fresh locked data
            if ($lockedOffer->usage_limit_total !== null && $lockedOffer->used_count >= $lockedOffer->usage_limit_total) {
                return ['success' => false, 'reason' => 'usage_limit_reached', 'usage' => null];
            }

            $userId = $context['userId'];
            if ($lockedOffer->usage_limit_per_user !== null) {
                $userCount = OfferUsage::where('offer_id', $lockedOffer->id)
                    ->where('user_id', $userId)
                    ->active()
                    ->count();

                if ($userCount >= $lockedOffer->usage_limit_per_user) {
                    return ['success' => false, 'reason' => 'per_user_limit_reached', 'usage' => null];
                }
            }

            // Create usage record
            $usage = OfferUsage::create([
                'offer_id' => $lockedOffer->id,
                'user_id' => $userId,
                'booking_type' => $bookingData['booking_type'],
                'booking_id' => $bookingData['booking_id'],
                'service_id' => $context['serviceId'] ?? null,
                'branch_id' => $context['branchId'] ?? null,
                'original_amount' => $bookingData['original_amount'],
                'discount_amount' => $bookingData['discount_amount'],
                'final_amount' => $bookingData['final_amount'],
                'coupon_code' => $lockedOffer->code,
                'status' => OfferUsage::STATUS_APPLIED,
            ]);

            // Atomically increment
            $lockedOffer->increment('used_count');

            return ['success' => true, 'reason' => null, 'usage' => $usage];
        });
    }

    /**
     * Atomically reverse a usage (for cancellation/refund).
     */
    public function reverseUsage(OfferUsage $usage, string $reason = 'cancelled'): bool
    {
        return DB::transaction(function () use ($usage, $reason) {
            $status = $reason === 'refund' ? OfferUsage::STATUS_REVERSED : OfferUsage::STATUS_CANCELLED;

            $usage->update([
                'status' => $status,
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            Offer::where('id', $usage->offer_id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');

            return true;
        });
    }

    /**
     * Returns all valid offers for a given booking context.
     */
    public function getEligibleOffers(
        int $userId,
        int $serviceId,
        ?int $branchId,
        ?int $clinicId,
        float $serviceCharge,
        string $channel = 'web',
        ?string $paymentMethod = null,
        ?string $couponCode = null
    ): Collection {
        $context = compact('userId', 'serviceId', 'branchId', 'clinicId', 'serviceCharge', 'channel', 'paymentMethod', 'couponCode');

        $query = Offer::where('is_active', true)
            ->where('status', Offer::STATUS_ACTIVE);

        // For auto-apply: exclude coupon-required offers unless a code is provided
        if ($couponCode) {
            $query->where(function ($q) use ($couponCode) {
                $q->where('requires_coupon', false)
                  ->orWhere('code', $couponCode);
            });
        } else {
            $query->where('requires_coupon', false);
        }

        $offers = $query->with(['services', 'branches', 'categories', 'customerSegments', 'schedules'])->get();

        return $offers->filter(function (Offer $offer) use ($context) {
            $result = $this->validateOffer($offer, $context);
            return $result['valid'];
        })->values();
    }

    /**
     * Validates a single offer against booking context.
     * Returns ['valid' => bool, 'reason' => string|null]
     */
    public function validateOffer(Offer $offer, array $context): array
    {
        $steps = [
            'checkStatus',
            'checkDateRange',
            'checkSchedule',
            'checkVendor',
            'checkBranch',
            'checkService',
            'checkCategory',
            'checkCustomerScope',
            'checkFirstBooking',
            'checkMinimumAmount',
            'checkCoupon',
            'checkChannel',
            'checkPaymentMethod',
            'checkUsageLimitTotal',
            'checkUsageLimitPerUser',
            'checkSegment',
        ];

        foreach ($steps as $step) {
            $result = $this->$step($offer, $context);
            if (! $result['valid']) {
                return $result;
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Validates a coupon code and returns the offer if valid.
     */
    public function validateCoupon(string $code, array $context): ?Offer
    {
        $offer = Offer::where('code', $code)
            ->where('requires_coupon', true)
            ->with(['services', 'branches', 'categories', 'customerSegments', 'schedules'])
            ->first();

        if (! $offer) {
            return null;
        }

        $result = $this->validateOffer($offer, $context);

        return $result['valid'] ? $offer : null;
    }

    /**
     * Validates a coupon code with detailed error info.
     */
    public function validateCouponWithReason(string $code, array $context): array
    {
        $offer = Offer::where('code', $code)
            ->with(['services', 'branches', 'categories', 'customerSegments', 'schedules'])
            ->first();

        if (! $offer) {
            return ['valid' => false, 'reason' => 'invalid_code', 'offer' => null];
        }

        $result = $this->validateOffer($offer, $context);

        return [
            'valid' => $result['valid'],
            'reason' => $result['reason'],
            'offer' => $result['valid'] ? $offer : null,
        ];
    }

    // --- Validation Steps (short-circuit pipeline) ---

    protected function checkStatus(Offer $offer, array $context): array
    {
        if (! $offer->is_active || $offer->status !== Offer::STATUS_ACTIVE) {
            return ['valid' => false, 'reason' => 'offer_inactive'];
        }
        return ['valid' => true, 'reason' => null];
    }

    protected function checkDateRange(Offer $offer, array $context): array
    {
        $now = now();
        if ($offer->starts_at && $now->lt($offer->starts_at)) {
            return ['valid' => false, 'reason' => 'not_started'];
        }
        if ($offer->ends_at && $now->gt($offer->ends_at)) {
            return ['valid' => false, 'reason' => 'expired'];
        }
        return ['valid' => true, 'reason' => null];
    }

    protected function checkSchedule(Offer $offer, array $context): array
    {
        if ($offer->schedules->isEmpty()) {
            return ['valid' => true, 'reason' => null];
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeek; // 0=Sun, 6=Sat
        $currentTime = $now->format('H:i');

        foreach ($offer->schedules as $schedule) {
            $dayMatch = is_null($schedule->day_of_week) || $schedule->day_of_week == $dayOfWeek;
            $timeMatch = true;

            $startTime = $schedule->start_time ? substr($schedule->start_time, 0, 5) : null;
            $endTime = $schedule->end_time ? substr($schedule->end_time, 0, 5) : null;

            if ($startTime && $currentTime < $startTime) {
                $timeMatch = false;
            }
            if ($endTime && $currentTime > $endTime) {
                $timeMatch = false;
            }

            if ($dayMatch && $timeMatch) {
                return ['valid' => true, 'reason' => null];
            }
        }

        return ['valid' => false, 'reason' => 'outside_schedule'];
    }

    protected function checkVendor(Offer $offer, array $context): array
    {
        if (is_null($offer->vendor_id)) {
            return ['valid' => true, 'reason' => null]; // Platform-wide
        }

        // Resolve vendor from clinic
        $clinicId = $context['clinicId'] ?? null;
        if ($clinicId) {
            $clinic = \Modules\Clinic\Models\Clinics::find($clinicId);
            if ($clinic && $clinic->vendor_id != $offer->vendor_id) {
                return ['valid' => false, 'reason' => 'vendor_mismatch'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    protected function checkBranch(Offer $offer, array $context): array
    {
        if ($offer->applies_to_all_branches) {
            return ['valid' => true, 'reason' => null];
        }

        $branchId = $context['branchId'] ?? null;
        if (! $branchId) {
            // Try to resolve from clinic
            $clinicId = $context['clinicId'] ?? null;
            if ($clinicId) {
                $clinic = \Modules\Clinic\Models\Clinics::find($clinicId);
                $branchId = $clinic->branch_id ?? null;
            }
        }

        if (! $branchId) {
            return ['valid' => false, 'reason' => 'branch_not_matched'];
        }

        $matched = $offer->branches->contains('branch_id', $branchId);
        return $matched
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'branch_not_matched'];
    }

    protected function checkService(Offer $offer, array $context): array
    {
        if ($offer->applies_to_all_services) {
            return ['valid' => true, 'reason' => null];
        }

        $serviceId = $context['serviceId'];
        $matched = $offer->services->contains('service_id', $serviceId);

        return $matched
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'service_not_matched'];
    }

    protected function checkCategory(Offer $offer, array $context): array
    {
        if ($offer->applies_to_all_categories) {
            return ['valid' => true, 'reason' => null];
        }

        $serviceId = $context['serviceId'];
        $service = \Modules\Clinic\Models\ClinicsService::find($serviceId);
        $categoryId = $service->category_id ?? null;

        if (! $categoryId) {
            return ['valid' => false, 'reason' => 'category_not_matched'];
        }

        $matched = $offer->categories->contains('category_id', $categoryId);
        return $matched
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'category_not_matched'];
    }

    protected function checkCustomerScope(Offer $offer, array $context): array
    {
        if ($offer->customer_scope === Offer::SCOPE_ALL) {
            return ['valid' => true, 'reason' => null];
        }

        $userId = $context['userId'];
        $completedBookings = $this->getCompletedBookingCount($userId);

        if ($offer->customer_scope === Offer::SCOPE_FIRST_TIME && $completedBookings > 0) {
            return ['valid' => false, 'reason' => 'not_first_time_customer'];
        }

        if ($offer->customer_scope === Offer::SCOPE_RETURNING) {
            if ($completedBookings < $offer->min_completed_bookings) {
                return ['valid' => false, 'reason' => 'insufficient_bookings'];
            }
            if ($offer->max_completed_bookings && $completedBookings > $offer->max_completed_bookings) {
                return ['valid' => false, 'reason' => 'exceeded_booking_cap'];
            }
        }

        // Segment check deferred to step 16
        return ['valid' => true, 'reason' => null];
    }

    protected function checkFirstBooking(Offer $offer, array $context): array
    {
        if (! $offer->first_booking_only) {
            return ['valid' => true, 'reason' => null];
        }

        $userId = $context['userId'];
        $serviceId = $context['serviceId'];

        // Check if user has any completed booking for this specific service
        $hasBooked = \Modules\Appointment\Models\Appointment::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->whereIn('status', ['completed', 'check_in'])
            ->exists();

        return $hasBooked
            ? ['valid' => false, 'reason' => 'not_first_booking_for_service']
            : ['valid' => true, 'reason' => null];
    }

    protected function checkMinimumAmount(Offer $offer, array $context): array
    {
        if ($offer->minimum_order_amount > 0 && $context['serviceCharge'] < $offer->minimum_order_amount) {
            return ['valid' => false, 'reason' => 'below_minimum_amount'];
        }
        return ['valid' => true, 'reason' => null];
    }

    protected function checkCoupon(Offer $offer, array $context): array
    {
        if (! $offer->requires_coupon) {
            return ['valid' => true, 'reason' => null];
        }

        $couponCode = $context['couponCode'] ?? null;
        if (! $couponCode || $couponCode !== $offer->code) {
            return ['valid' => false, 'reason' => 'coupon_required'];
        }

        return ['valid' => true, 'reason' => null];
    }

    protected function checkChannel(Offer $offer, array $context): array
    {
        if (empty($offer->allowed_channels)) {
            return ['valid' => true, 'reason' => null];
        }

        $channel = $context['channel'] ?? 'web';
        return in_array($channel, $offer->allowed_channels)
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'channel_not_allowed'];
    }

    protected function checkPaymentMethod(Offer $offer, array $context): array
    {
        if (empty($offer->allowed_payment_methods)) {
            return ['valid' => true, 'reason' => null];
        }

        $method = $context['paymentMethod'] ?? null;
        if (! $method) {
            return ['valid' => true, 'reason' => null]; // Not yet selected
        }

        return in_array($method, $offer->allowed_payment_methods)
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'payment_method_not_allowed'];
    }

    protected function checkUsageLimitTotal(Offer $offer, array $context): array
    {
        if (is_null($offer->usage_limit_total)) {
            return ['valid' => true, 'reason' => null];
        }

        // Fresh read from DB to avoid stale model attribute
        $currentCount = Offer::where('id', $offer->id)->value('used_count') ?? 0;

        return $currentCount < $offer->usage_limit_total
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'usage_limit_reached'];
    }

    protected function checkUsageLimitPerUser(Offer $offer, array $context): array
    {
        if (is_null($offer->usage_limit_per_user)) {
            return ['valid' => true, 'reason' => null];
        }

        $userId = $context['userId'];
        $userUsageCount = OfferUsage::where('offer_id', $offer->id)
            ->where('user_id', $userId)
            ->active()
            ->count();

        return $userUsageCount < $offer->usage_limit_per_user
            ? ['valid' => true, 'reason' => null]
            : ['valid' => false, 'reason' => 'per_user_limit_reached'];
    }

    protected function checkSegment(Offer $offer, array $context): array
    {
        if ($offer->customer_scope !== Offer::SCOPE_SEGMENT) {
            return ['valid' => true, 'reason' => null];
        }

        if ($offer->customerSegments->isEmpty()) {
            return ['valid' => true, 'reason' => null];
        }

        $userId = $context['userId'];
        $user = \App\Models\User::find($userId);

        if (! $user) {
            return ['valid' => false, 'reason' => 'segment_not_matched'];
        }

        foreach ($offer->customerSegments as $segment) {
            if ($segment->segment_type === 'role' && $user->hasRole($segment->segment_value)) {
                return ['valid' => true, 'reason' => null];
            }
            // Additional segment types can be added here
        }

        return ['valid' => false, 'reason' => 'segment_not_matched'];
    }

    // --- Helpers ---

    protected function getCompletedBookingCount(int $userId): int
    {
        $count = 0;

        // Appointments
        $count += \Modules\Appointment\Models\Appointment::where('user_id', $userId)
            ->whereIn('status', ['completed', 'check_in'])
            ->count();

        // Nurse requests
        if (class_exists(\Modules\RequestNurse\Models\NurseRequest::class)) {
            $count += \Modules\RequestNurse\Models\NurseRequest::where('user_id', $userId)
                ->where('status', 'completed')
                ->count();
        }

        // Test orders
        if (class_exists(\Modules\RadiologyLab\Models\TestOrder::class)) {
            $count += \Modules\RadiologyLab\Models\TestOrder::where('patient_id', $userId)
                ->where('status', 'completed')
                ->count();
        }

        return $count;
    }
}
