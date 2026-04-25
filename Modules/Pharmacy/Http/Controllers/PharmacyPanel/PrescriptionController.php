<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PrescriptionRequest;
use Modules\Pharmacy\Models\PharmacyPrescription;
use Modules\Pharmacy\Services\PharmacyNotificationService;

class PrescriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_prescriptions')->only(['index', 'index_data', 'show']);
        $this->middleware('can:review_pharmacy_prescriptions')->only(['review']);
    }

    public function index()
    {
        $page_title = 'Prescriptions';
        $page_title_icon = 'fa fa-prescription';

        return view('pharmacy::backend.pharmacy-panel.prescriptions.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyPrescription::with('user')
            ->where('pharmacy_id', $pharmacyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $prescriptions = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $prescriptions->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $prescriptions->currentPage(),
            'last_page' => $prescriptions->lastPage(),
        ]);
    }

    public function show($id)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $prescription = PharmacyPrescription::with(['user', 'reviewer'])
            ->where('pharmacy_id', $pharmacyId)
            ->findOrFail($id);
        $page_title = "Prescription #{$prescription->prescription_number}";

        return view('pharmacy::backend.pharmacy-panel.prescriptions.show', compact('page_title', 'prescription'));
    }

    public function review($id, PrescriptionRequest $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $prescription = PharmacyPrescription::where('pharmacy_id', $pharmacyId)
            ->whereIn('status', [PharmacyPrescription::STATUS_PENDING, PharmacyPrescription::STATUS_UNDER_REVIEW])
            ->findOrFail($id);

        $prescription->update([
            'status' => $request->status === 'approved'
                ? PharmacyPrescription::STATUS_APPROVED
                : PharmacyPrescription::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        (new PharmacyNotificationService)->notifyPrescriptionStatus($prescription);

        return response()->json(['status' => true, 'message' => 'Prescription reviewed successfully']);
    }
}
