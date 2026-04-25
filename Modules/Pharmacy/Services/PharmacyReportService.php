<?php

namespace Modules\Pharmacy\Services;

use Illuminate\Support\Facades\DB;
use Modules\Pharmacy\Models\PharmacyExpiryAlert;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Models\PharmacyPrescription;
use Modules\Pharmacy\Models\PharmacyStockAlert;

class PharmacyReportService
{
    public function getDashboardData(int $pharmacyId): array
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $todayOrders = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->whereDate('created_at', $today)
            ->count();

        $todayRevenue = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->whereDate('created_at', $today)
            ->where('status', PharmacyOrder::STATUS_DELIVERED)
            ->sum('total');

        $pendingOrders = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->where('status', PharmacyOrder::STATUS_PENDING)
            ->count();

        $lowStockCount = PharmacyStockAlert::where('pharmacy_id', $pharmacyId)
            ->where('is_resolved', false)
            ->count();

        $expiringSoonCount = PharmacyExpiryAlert::where('pharmacy_id', $pharmacyId)
            ->where('is_resolved', false)
            ->where('alert_type', 'expiring_soon')
            ->count();

        $pendingPrescriptions = PharmacyPrescription::where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();

        $monthlyRevenue = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->where('created_at', '>=', $monthStart)
            ->where('status', PharmacyOrder::STATUS_DELIVERED)
            ->sum('total');

        $topProducts = DB::table('pharmacy_order_items')
            ->join('pharmacy_orders', 'pharmacy_orders.id', '=', 'pharmacy_order_items.order_id')
            ->where('pharmacy_orders.pharmacy_id', $pharmacyId)
            ->where('pharmacy_orders.status', PharmacyOrder::STATUS_DELIVERED)
            ->where('pharmacy_orders.created_at', '>=', $monthStart)
            ->select(
                'pharmacy_order_items.product_id',
                'pharmacy_order_items.product_name as name',
                DB::raw('SUM(pharmacy_order_items.quantity) as quantity_sold')
            )
            ->groupBy('pharmacy_order_items.product_id', 'pharmacy_order_items.product_name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get()
            ->toArray();

        $statusBreakdown = PharmacyOrder::where('pharmacy_id', $pharmacyId)
            ->where('created_at', '>=', $monthStart)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'today_orders' => $todayOrders,
            'today_revenue' => (float) $todayRevenue,
            'pending_orders' => $pendingOrders,
            'low_stock_count' => $lowStockCount,
            'expiring_soon_count' => $expiringSoonCount,
            'pending_prescriptions' => $pendingPrescriptions,
            'monthly_revenue' => (float) $monthlyRevenue,
            'top_products' => $topProducts,
            'order_status_breakdown' => $statusBreakdown,
        ];
    }

    public function getRevenueReport(array $filters = []): array
    {
        $query = PharmacyOrder::where('status', PharmacyOrder::STATUS_DELIVERED);

        if (! empty($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }
        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        $groupBy = $filters['group_by'] ?? 'daily';
        $dateFormat = match ($groupBy) {
            'monthly' => '%Y-%m',
            'weekly' => '%Y-%u',
            default => '%Y-%m-%d',
        };

        return $query->select(
            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('SUM(delivery_fee) as total_delivery_fees'),
            DB::raw('SUM(coupon_discount) as total_discounts')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }
}
