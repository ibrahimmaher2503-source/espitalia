<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyPayoutRequest;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCommissionEarning;
use Modules\Pharmacy\Models\PharmacyPayout;

class PharmacyEarningController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Earnings';
        $this->module_name = 'pharmacy_earnings';
        $this->module_path = 'pharmacy::backend.earnings';

        $this->middleware('can:view_pharmacy_earning')->only(['index', 'index_data', 'show', 'show_data']);
        $this->middleware('can:edit_pharmacy_earning')->only(['payout']);
    }

    /**
     * Display earnings dashboard with summary stats
     */
    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        // Dashboard summary stats (excluding cancelled)
        $totalPlatformCommission = PharmacyCommissionEarning::where('status', '!=', 'cancelled')->sum('commission_amount');
        $totalUnpaidReceivable = PharmacyCommissionEarning::where('status', 'unpaid')->sum('pharmacy_receivable');
        $totalPaidReceivable = PharmacyCommissionEarning::where('status', 'paid')->sum('pharmacy_receivable');

        return view('pharmacy::backend.earnings.index', compact(
            'page_title',
            'page_title_icon',
            'totalPlatformCommission',
            'totalUnpaidReceivable',
            'totalPaidReceivable'
        ));
    }

    /**
     * Get aggregated earnings data by pharmacy for DataTable
     */
    public function index_data(Request $request)
    {
        $query = Pharmacy::query();

        // Vendor scoping — vendors can only see their own pharmacies
        $user = auth()->user();
        if ($user->hasRole('vendor')) {
            $query->where('vendor_id', $user->id);
        } elseif ($user->hasRole('pharmacy')) {
            $query->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $user->id));
        }

        // Search by pharmacy name
        if ($request->has('search') && ! empty($request->search)) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('name', 'like', "%$search%");
        }

        // Add aggregated earning data via subqueries (excluding cancelled)
        $query->addSelect([
            'total_platform_commission' => PharmacyCommissionEarning::selectRaw('COALESCE(SUM(commission_amount), 0)')
                ->whereColumn('pharmacy_id', 'pharmacies.id')
                ->where('status', '!=', 'cancelled'),
            'total_unpaid_receivable' => PharmacyCommissionEarning::selectRaw('COALESCE(SUM(pharmacy_receivable), 0)')
                ->whereColumn('pharmacy_id', 'pharmacies.id')
                ->where('status', 'unpaid'),
            'total_paid_receivable' => PharmacyCommissionEarning::selectRaw('COALESCE(SUM(pharmacy_receivable), 0)')
                ->whereColumn('pharmacy_id', 'pharmacies.id')
                ->where('status', 'paid'),
            'total_orders' => PharmacyCommissionEarning::selectRaw('COUNT(*)')
                ->whereColumn('pharmacy_id', 'pharmacies.id')
                ->where('status', '!=', 'cancelled'),
        ]);

        $total = $query->count();
        $perPage = min((int) $request->get('per_page', 10), 50);
        $pharmacies = $query->orderByDesc('created_at')->paginate($perPage);

        $data = $pharmacies->map(function ($pharmacy) {
            return [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'total_orders' => $pharmacy->total_orders,
                'total_platform_commission' => $pharmacy->total_platform_commission,
                'total_unpaid_receivable' => $pharmacy->total_unpaid_receivable,
                'total_paid_receivable' => $pharmacy->total_paid_receivable,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $pharmacies->currentPage(),
            'last_page' => $pharmacies->lastPage(),
        ]);
    }

    /**
     * Show per-pharmacy earning detail page with payout history
     */
    public function show($pharmacyId)
    {
        $pharmacy = Pharmacy::findOrFail($pharmacyId);

        // Vendor scoping: vendors can only view their own pharmacies
        $user = auth()->user();
        if ($user->hasRole('vendor') && $pharmacy->vendor_id !== $user->id) {
            abort(403);
        }

        $page_title = "Earnings for {$pharmacy->name}";
        $page_title_icon = 'fa fa-eye';

        // Summary stats for this pharmacy
        $totalCommission = PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
            ->sum('commission_amount');
        $totalUnpaid = PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
            ->where('status', 'unpaid')
            ->sum('pharmacy_receivable');
        $totalPaid = PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
            ->where('status', 'paid')
            ->sum('pharmacy_receivable');

        return view('pharmacy::backend.earnings.show', compact(
            'page_title',
            'page_title_icon',
            'pharmacy',
            'totalCommission',
            'totalUnpaid',
            'totalPaid'
        ));
    }

    /**
     * Get earning records for a specific pharmacy (DataTable)
     */
    public function show_data($pharmacyId, Request $request)
    {
        // Verify vendor owns this pharmacy
        $user = auth()->user();
        if ($user->hasRole('vendor')) {
            $pharmacy = Pharmacy::where('id', $pharmacyId)->where('vendor_id', $user->id)->first();
            if (! $pharmacy) {
                abort(403);
            }
        }

        $query = PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
            ->with('order');

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
     * Get payout history for a specific pharmacy
     */
    public function payouts_data($pharmacyId)
    {
        $payouts = PharmacyPayout::where('pharmacy_id', $pharmacyId)
            ->orderByDesc('payment_date')
            ->get();

        return response()->json($payouts);
    }

    /**
     * Process payout for a pharmacy (mark unpaid earnings as paid)
     */
    public function payout($pharmacyId, PharmacyPayoutRequest $request)
    {
        try {
            return \DB::transaction(function () use ($pharmacyId, $request) {
                // Get all unpaid earnings for this pharmacy (locked to prevent concurrent payouts)
                $unpaidEarnings = PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
                    ->where('status', 'unpaid')
                    ->lockForUpdate()
                    ->get();

                if ($unpaidEarnings->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No unpaid earnings to process',
                    ], 422);
                }

                // Calculate totals
                $totalAmount = $unpaidEarnings->sum('pharmacy_receivable');
                $totalCommission = $unpaidEarnings->sum('commission_amount');

                // Create payout record
                $payout = PharmacyPayout::create([
                    'pharmacy_id' => $pharmacyId,
                    'total_amount' => $totalAmount,
                    'total_commission' => $totalCommission,
                    'payment_method' => $request->input('payment_method'),
                    'payment_date' => now()->toDateString(),
                    'notes' => $request->input('notes'),
                ]);

                // Update all earnings to paid and link to payout
                PharmacyCommissionEarning::where('pharmacy_id', $pharmacyId)
                    ->where('status', 'unpaid')
                    ->update([
                        'status' => 'paid',
                        'payout_id' => $payout->id,
                    ]);

                return response()->json([
                    'status' => true,
                    'message' => __('pharmacy.msg_payout_processed'),
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
