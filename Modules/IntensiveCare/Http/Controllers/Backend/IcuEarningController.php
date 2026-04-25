<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IntensiveCare\Http\Requests\IcuPayoutRequest;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuCommissionEarning;
use Modules\IntensiveCare\Models\IcuPayout;

class IcuEarningController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'ICU Earnings';
        $this->module_name = 'icu_earnings';
        $this->module_path = 'intensivecare::backend.earnings';

        $this->middleware('can:view_icu_earning')->only(['index', 'index_data', 'show', 'show_data']);
        $this->middleware('can:edit_icu_earning')->only(['payout']);
    }

    /**
     * Display earnings dashboard with summary stats
     */
    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        $query = IcuCommissionEarning::query();

        // Hospital coordinator scope: filter to their hospital via vendor_id
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospitalIds = Hospital::where('vendor_id', auth()->id())->pluck('id');
            $query->whereIn('hospital_id', $hospitalIds);
        }

        // Dashboard summary stats (excluding cancelled)
        $totalPlatformCommission = (clone $query)->where('status', '!=', 'cancelled')->sum('commission_amount');
        $totalUnpaidReceivable = (clone $query)->where('status', 'unpaid')->sum('hospital_receivable');
        $totalPaidReceivable = (clone $query)->where('status', 'paid')->sum('hospital_receivable');

        return view('intensivecare::backend.earnings.index', compact(
            'page_title',
            'page_title_icon',
            'totalPlatformCommission',
            'totalUnpaidReceivable',
            'totalPaidReceivable'
        ));
    }

    /**
     * Get aggregated earnings data by hospital for DataTable
     */
    public function index_data(Request $request)
    {
        $query = Hospital::query();

        // Hospital coordinator scope: filter to their hospital via vendor_id
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $query->where('vendor_id', auth()->id());
        }

        // Search by hospital name (both name_ar and name_en)
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        // Add aggregated earning data via subqueries (excluding cancelled)
        $query->addSelect([
            'hospitals.*',
            'total_platform_commission' => IcuCommissionEarning::selectRaw('COALESCE(SUM(commission_amount), 0)')
                ->whereColumn('hospital_id', 'hospitals.id')
                ->where('status', '!=', 'cancelled'),
            'total_unpaid_receivable' => IcuCommissionEarning::selectRaw('COALESCE(SUM(hospital_receivable), 0)')
                ->whereColumn('hospital_id', 'hospitals.id')
                ->where('status', 'unpaid'),
            'total_paid_receivable' => IcuCommissionEarning::selectRaw('COALESCE(SUM(hospital_receivable), 0)')
                ->whereColumn('hospital_id', 'hospitals.id')
                ->where('status', 'paid'),
            'total_orders' => IcuCommissionEarning::selectRaw('COUNT(*)')
                ->whereColumn('hospital_id', 'hospitals.id')
                ->where('status', '!=', 'cancelled'),
        ]);

        $total = $query->count();
        $perPage = min((int) $request->get('per_page', 10), 50);
        $hospitals = $query->orderByDesc('created_at')->paginate($perPage);

        $data = $hospitals->map(function ($hospital) {
            return [
                'id' => $hospital->id,
                'name' => $hospital->name,
                'total_orders' => $hospital->total_orders,
                'total_platform_commission' => $hospital->total_platform_commission,
                'total_unpaid_receivable' => $hospital->total_unpaid_receivable,
                'total_paid_receivable' => $hospital->total_paid_receivable,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $hospitals->currentPage(),
            'last_page' => $hospitals->lastPage(),
        ]);
    }

    /**
     * Show per-hospital earning detail page with payout history
     */
    public function show($hospitalId)
    {
        $hospital = Hospital::findOrFail($hospitalId);

        // Hospital coordinator scope: ensure they can only view their own hospital
        if (auth()->user()->hasRole('hospital_coordinator') && $hospital->vendor_id !== auth()->id()) {
            abort(403);
        }

        $page_title = "Earnings for {$hospital->name}";
        $page_title_icon = 'fa fa-eye';

        // Summary stats for this hospital
        $totalCommission = IcuCommissionEarning::where('hospital_id', $hospitalId)
            ->sum('commission_amount');
        $totalUnpaid = IcuCommissionEarning::where('hospital_id', $hospitalId)
            ->where('status', 'unpaid')
            ->sum('hospital_receivable');
        $totalPaid = IcuCommissionEarning::where('hospital_id', $hospitalId)
            ->where('status', 'paid')
            ->sum('hospital_receivable');

        return view('intensivecare::backend.earnings.show', compact(
            'page_title',
            'page_title_icon',
            'hospital',
            'totalCommission',
            'totalUnpaid',
            'totalPaid'
        ));
    }

    /**
     * Get earning records for a specific hospital (DataTable)
     */
    public function show_data($hospitalId, Request $request)
    {
        // Hospital coordinator scope
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::findOrFail($hospitalId);
            if ($hospital->vendor_id !== auth()->id()) {
                abort(403);
            }
        }

        $query = IcuCommissionEarning::where('hospital_id', $hospitalId)
            ->with('admissionRequest');

        // Filter by status
        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && ! empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && ! empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $total = $query->count();
        $perPage = min((int) $request->get('per_page', 10), 50);
        $earnings = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $earnings->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $earnings->currentPage(),
            'last_page' => $earnings->lastPage(),
        ]);
    }

    /**
     * Get payout history for a specific hospital
     */
    public function payouts_data($hospitalId)
    {
        // Hospital coordinator scope
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::findOrFail($hospitalId);
            if ($hospital->vendor_id !== auth()->id()) {
                abort(403);
            }
        }

        $payouts = IcuPayout::where('hospital_id', $hospitalId)
            ->orderByDesc('payment_date')
            ->get();

        return response()->json($payouts);
    }

    /**
     * Process payout for a hospital (mark unpaid earnings as paid)
     */
    public function payout($hospitalId, IcuPayoutRequest $request)
    {
        // Hospital coordinator scope
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::findOrFail($hospitalId);
            if ($hospital->vendor_id !== auth()->id()) {
                abort(403);
            }
        }

        try {
            return \DB::transaction(function () use ($hospitalId, $request) {
                // Get all unpaid earnings for this hospital (locked to prevent concurrent payouts)
                $unpaidEarnings = IcuCommissionEarning::where('hospital_id', $hospitalId)
                    ->where('status', 'unpaid')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidEarnings->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => __('intensive_care.msg_no_unpaid_earnings'),
                    ], 422);
                }

                // Calculate totals
                $totalAmount = $unpaidEarnings->sum('hospital_receivable');
                $totalCommission = $unpaidEarnings->sum('commission_amount');

                // Create payout record
                $payout = IcuPayout::create([
                    'hospital_id' => $hospitalId,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'payment_method' => $request->input('payment_method'),
                    'payment_date' => now()->toDateString(),
                    'notes' => $request->input('notes'),
                ]);

                // Update all earnings to paid and link to payout
                IcuCommissionEarning::where('hospital_id', $hospitalId)
                    ->where('status', 'unpaid')
                    ->update([
                        'status' => 'paid',
                        'payout_id' => $payout->id,
                    ]);

                return response()->json([
                    'status' => true,
                    'message' => __('intensive_care.msg_payout_processed'),
                    'payout' => $payout,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
