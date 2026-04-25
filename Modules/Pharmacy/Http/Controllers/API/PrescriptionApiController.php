<?php

namespace Modules\Pharmacy\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PrescriptionRequest;
use Modules\Pharmacy\Models\PharmacyPrescription;
use Modules\Pharmacy\Services\PharmacyNotificationService;
use Modules\Pharmacy\Transformers\PrescriptionResource;

class PrescriptionApiController extends Controller
{
    /**
     * Upload a new prescription (patient)
     */
    public function store(PrescriptionRequest $request)
    {
        $prescription = PharmacyPrescription::create([
            'user_id' => auth()->id(),
            'pharmacy_id' => $request->pharmacy_id,
            'doctor_name' => $request->doctor_name,
            'notes' => $request->notes,
            'status' => PharmacyPrescription::STATUS_PENDING,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $prescription->addMedia($image)->toMediaCollection('prescription_images');
            }
        }

        if ($request->pharmacy_id) {
            $patientName = auth()->user()->first_name.' '.auth()->user()->last_name;
            (new PharmacyNotificationService)->notify(
                $request->pharmacy_id,
                null,
                'prescription_uploaded',
                [
                    'title_en' => 'New Prescription #'.$prescription->prescription_number,
                    'title_ar' => 'وصفة جديدة #'.$prescription->prescription_number,
                    'body_en' => 'Prescription uploaded by '.$patientName,
                    'body_ar' => 'وصفة مرفوعة من '.$patientName,
                    'extra' => [
                        'prescription_id' => $prescription->id,
                        'prescription_number' => $prescription->prescription_number,
                    ],
                ]
            );
        }

        return response()->json([
            'status' => true,
            'data' => new PrescriptionResource($prescription->load('user', 'pharmacy')),
            'message' => 'Prescription uploaded successfully',
        ], 201);
    }

    /**
     * List patient's own prescriptions
     */
    public function index(Request $request)
    {
        $query = PharmacyPrescription::where('user_id', auth()->id())
            ->with('pharmacy');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $prescriptions = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => PrescriptionResource::collection($prescriptions),
            'meta' => [
                'current_page' => $prescriptions->currentPage(),
                'per_page' => $prescriptions->perPage(),
                'total' => $prescriptions->total(),
                'last_page' => $prescriptions->lastPage(),
            ],
        ]);
    }

    /**
     * Show prescription detail (patient)
     */
    public function show($id)
    {
        $prescription = PharmacyPrescription::where('user_id', auth()->id())
            ->with('pharmacy', 'reviewer', 'order')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new PrescriptionResource($prescription),
        ]);
    }
}
