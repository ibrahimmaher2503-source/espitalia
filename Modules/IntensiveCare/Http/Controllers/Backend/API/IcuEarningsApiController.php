<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuCommissionEarning;

class IcuEarningsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            // Only admin/demo_admin and vendor (hospital coordinator) can view earnings
            if (! $user->hasAnyRole(['admin', 'demo_admin', 'vendor'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to perform this action',
                ], 403);
            }

            $query = Hospital::query()->select('hospitals.*');

            // Hospital coordinators (vendors) see only their hospital
            if ($user->hasRole('vendor')) {
                $query->where('vendor_id', $user->id);
            }

            // Admin can filter by specific hospital
            if ($request->filled('hospital_id') && $user->hasAnyRole(['admin', 'demo_admin'])) {
                $query->where('hospitals.id', $request->hospital_id);
            }

            // Build date filter closure for reuse
            $dateFilter = function ($subQuery) use ($request) {
                $subQuery->whereColumn('hospital_id', 'hospitals.id')
                    ->where('status', '!=', IcuCommissionEarning::STATUS_CANCELLED);

                if ($request->filled('start_date')) {
                    $subQuery->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $subQuery->whereDate('created_at', '<=', $request->end_date);
                }
            };

            // Add aggregated earning data via subqueries (excluding cancelled)
            $query->addSelect([
                'total_platform_commission' => IcuCommissionEarning::selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('hospital_id', 'hospitals.id')
                    ->where('status', '!=', IcuCommissionEarning::STATUS_CANCELLED)
                    ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->end_date)),
                'total_unpaid_receivable' => IcuCommissionEarning::selectRaw('COALESCE(SUM(hospital_receivable), 0)')
                    ->whereColumn('hospital_id', 'hospitals.id')
                    ->where('status', IcuCommissionEarning::STATUS_UNPAID)
                    ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->end_date)),
                'total_paid_receivable' => IcuCommissionEarning::selectRaw('COALESCE(SUM(hospital_receivable), 0)')
                    ->whereColumn('hospital_id', 'hospitals.id')
                    ->where('status', IcuCommissionEarning::STATUS_PAID)
                    ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->end_date)),
                'total_orders' => IcuCommissionEarning::selectRaw('COUNT(*)')
                    ->whereColumn('hospital_id', 'hospitals.id')
                    ->where('status', '!=', IcuCommissionEarning::STATUS_CANCELLED)
                    ->when($request->filled('start_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->start_date))
                    ->when($request->filled('end_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->end_date)),
            ]);

            $hospitals = $query->orderByDesc('created_at')->get();

            // Compute overall totals
            $totals = [
                'total_platform_commission' => number_format($hospitals->sum('total_platform_commission'), 2, '.', ''),
                'total_unpaid_receivable' => number_format($hospitals->sum('total_unpaid_receivable'), 2, '.', ''),
                'total_paid_receivable' => number_format($hospitals->sum('total_paid_receivable'), 2, '.', ''),
            ];

            $hospitalData = $hospitals->map(function ($hospital) {
                return [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'total_orders' => (int) $hospital->total_orders,
                    'total_platform_commission' => number_format((float) $hospital->total_platform_commission, 2, '.', ''),
                    'total_unpaid_receivable' => number_format((float) $hospital->total_unpaid_receivable, 2, '.', ''),
                    'total_paid_receivable' => number_format((float) $hospital->total_paid_receivable, 2, '.', ''),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Earnings summary',
                'data' => [
                    'totals' => $totals,
                    'hospitals' => $hospitalData,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
