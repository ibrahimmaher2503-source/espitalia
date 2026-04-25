<?php

namespace Modules\Offer\Services;

use Illuminate\Support\Facades\DB;
use Modules\Offer\Models\Offer;
use Modules\Offer\Models\OfferUsage;

class OfferReportService
{
    public function getDashboardStats(): array
    {
        return [
            'active_count' => Offer::active()->count(),
            'total_redemptions' => OfferUsage::active()->count(),
            'total_discount_given' => (float) OfferUsage::active()->sum('discount_amount'),
            'avg_discount_per_use' => (float) OfferUsage::active()->avg('discount_amount'),
        ];
    }

    public function getRedemptionTimeline(int $offerId, string $period = 'daily', ?string $from = null, ?string $to = null): array
    {
        $query = OfferUsage::where('offer_id', $offerId)->active();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $groupBy = match ($period) {
            'weekly' => DB::raw('YEARWEEK(created_at)'),
            'monthly' => DB::raw('DATE_FORMAT(created_at, "%Y-%m")'),
            default => DB::raw('DATE(created_at)'),
        };

        return $query->select($groupBy->getValue(DB::connection()->getQueryGrammar()).' as period', DB::raw('COUNT(*) as count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    public function getTopPerformingOffers(int $limit = 10): array
    {
        return OfferUsage::select('offer_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->active()
            ->groupBy('offer_id')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->with('offer:id,title_en,title_ar,code,discount_type')
            ->get()
            ->toArray();
    }

    public function getBranchPerformance(int $offerId): array
    {
        return OfferUsage::where('offer_id', $offerId)
            ->active()
            ->whereNotNull('branch_id')
            ->select('branch_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('branch_id')
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }

    public function getServicePerformance(int $offerId): array
    {
        return OfferUsage::where('offer_id', $offerId)
            ->active()
            ->whereNotNull('service_id')
            ->select('service_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('service_id')
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }

    public function getCustomerBreakdown(int $offerId): array
    {
        $usages = OfferUsage::where('offer_id', $offerId)->active()->get();
        $uniqueUsers = $usages->pluck('user_id')->unique();

        $newCustomers = 0;
        $returningCustomers = 0;

        foreach ($uniqueUsers as $userId) {
            $totalBookings = \Modules\Appointment\Models\Appointment::where('user_id', $userId)
                ->whereIn('status', ['completed', 'check_in'])
                ->count();

            if ($totalBookings <= 1) {
                $newCustomers++;
            } else {
                $returningCustomers++;
            }
        }

        return [
            'total_unique_users' => $uniqueUsers->count(),
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
        ];
    }

    public function getRevenueImpact(?string $from = null, ?string $to = null): array
    {
        $query = OfferUsage::active();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $result = $query->select(
            DB::raw('SUM(original_amount) as total_original'),
            DB::raw('SUM(discount_amount) as total_discount'),
            DB::raw('SUM(final_amount) as total_final'),
            DB::raw('COUNT(*) as total_usages')
        )->first();

        $originalRevenue = (float) ($result->total_original ?? 0);
        $discountGiven = (float) ($result->total_discount ?? 0);

        return [
            'original_revenue' => $originalRevenue,
            'total_discount' => $discountGiven,
            'discounted_revenue' => (float) ($result->total_final ?? 0),
            'discount_percentage' => $originalRevenue > 0 ? round($discountGiven / $originalRevenue * 100, 2) : 0,
            'total_usages' => (int) ($result->total_usages ?? 0),
        ];
    }

    public function getCouponUsageReport(): array
    {
        return OfferUsage::whereNotNull('coupon_code')
            ->active()
            ->select('coupon_code', DB::raw('COUNT(*) as usage_count'), DB::raw('COUNT(DISTINCT user_id) as unique_users'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('coupon_code')
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }

    public function getAbuseIndicators(): array
    {
        // Users with unusually high redemption counts (>10 in last 7 days)
        return OfferUsage::where('created_at', '>=', now()->subDays(7))
            ->active()
            ->select('user_id', DB::raw('COUNT(*) as usage_count'), DB::raw('SUM(discount_amount) as total_discount'))
            ->groupBy('user_id')
            ->having('usage_count', '>', 10)
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }

    public function getExpiringSoonOffers(int $days = 7): array
    {
        return Offer::active()
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDays($days))
            ->where('ends_at', '>', now())
            ->get()
            ->toArray();
    }

    public function getExhaustedOffers(): array
    {
        return Offer::where('status', Offer::STATUS_EXHAUSTED)
            ->get()
            ->toArray();
    }
}
