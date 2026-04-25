<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Models\PharmacyPrescription;

class PharmacyPrescriptionController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'Pharmacy Prescriptions';
        $this->module_name = 'pharmacy_prescriptions';
        $this->module_path = 'pharmacy::backend.prescriptions';

        $this->middleware('can:view_pharmacy_prescriptions');
    }

    public function index()
    {
        $page_title = $this->module_title;
        $page_title_icon = 'fa fa-list';

        return view('pharmacy::backend.prescriptions.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $query = PharmacyPrescription::with(['user', 'pharmacy', 'reviewer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%$search%")
                    ->orWhere('doctor_name', 'like', "%$search%")
                    ->orWhere('patient_name', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
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
        $prescription = PharmacyPrescription::with(['user', 'pharmacy', 'reviewer', 'order'])->findOrFail($id);
        $page_title = "Prescription #{$prescription->prescription_number}";
        $page_title_icon = 'fa fa-eye';

        return view('pharmacy::backend.prescriptions.show', compact('page_title', 'page_title_icon', 'prescription'));
    }
}
