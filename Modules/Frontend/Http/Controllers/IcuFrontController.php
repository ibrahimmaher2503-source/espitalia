<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\IntensiveCare\Http\Requests\IcuAdmissionRequestFormRequest;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuDepartment;

class IcuFrontController extends Controller
{
    public function hospitalCatalog()
    {
        $pageTitle = __('intensive_care.hospital_catalog');
        $specialtyTypes = IcuDepartment::SPECIALTY_TYPES;
        $equipmentLevels = IcuDepartment::EQUIPMENT_LEVELS;

        return view('frontend::icu_hospitals', compact('pageTitle', 'specialtyTypes', 'equipmentLevels'));
    }

    public function hospital_index_data(Request $request)
    {
        $query = Hospital::with('icuDepartments')->active();

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('specialty')) {
            $query->whereHas('icuDepartments', fn ($q) => $q->where('specialty_type', $request->specialty));
        }

        if ($request->filled('ventilator')) {
            $query->whereHas('icuDepartments', fn ($q) => $q->where('has_ventilator', true));
        }

        if ($request->filled('oxygen')) {
            $query->whereHas('icuDepartments', fn ($q) => $q->where('has_oxygen', true));
        }

        if ($request->filled('equipment_level')) {
            $query->whereHas('icuDepartments', fn ($q) => $q->where('equipment_level', $request->equipment_level));
        }

        if ($request->filled('insurance')) {
            $query->whereJsonContains('accepted_insurance', $request->insurance);
        }

        if ($request->filled('price_min')) {
            $min = $request->price_min;
            $query->whereHas('icuDepartments', fn ($q) => $q->where('daily_price', '>=', $min));
        }

        if ($request->filled('price_max')) {
            $max = $request->price_max;
            $query->whereHas('icuDepartments', fn ($q) => $q->where('daily_price', '<=', $max));
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('city', 'LIKE', "%$search%");
            });
        }

        $perPage = $request->per_page ?? 12;
        $hospitals = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $hospitals->map(function ($hospital) {
                $departments = $hospital->icuDepartments;

                return [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'city' => $hospital->city,
                    'hospital_type' => $hospital->hospital_type,
                    'hospital_type_label' => __('intensive_care.type_' . $hospital->hospital_type),
                    'rating' => $hospital->rating,
                    'phone' => $hospital->phone,
                    'total_available_beds' => $departments->sum('available_beds'),
                    'specialties' => $departments->pluck('specialty_type')->unique()->values()->toArray(),
                    'price_range' => $departments->count() > 0
                        ? number_format($departments->min('daily_price'), 2) . ' - ' . number_format($departments->max('daily_price'), 2)
                        : '-',
                    'accepted_insurance' => $hospital->accepted_insurance ?? [],
                    'profile_image' => $hospital->getFirstMediaUrl('logo') ?: asset('img/default-avatar.png'),
                ];
            }),
            'total' => $hospitals->total(),
            'last_page' => $hospitals->lastPage(),
        ]);
    }

    public function hospitalDetail($id)
    {
        $hospital = Hospital::with(['icuDepartments' => fn ($q) => $q->active()])->active()->findOrFail($id);
        $pageTitle = $hospital->name;

        return view('frontend::icu_hospital_detail', compact('hospital', 'pageTitle'));
    }

    public function requestForm(Request $request)
    {
        $pageTitle = __('intensive_care.admission_request');
        $hospitals = Hospital::active()->with('icuDepartments')->orderBy('name')->get();
        $selectedHospitalId = $request->hospital_id ?? null;

        return view('frontend::request_icu_admission', compact('pageTitle', 'hospitals', 'selectedHospitalId'));
    }

    public function store(IcuAdmissionRequestFormRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $admission = IcuAdmissionRequest::create([
                'patient_id' => auth()->id(),
                'hospital_id' => $request->hospital_id,
                'icu_department_id' => $request->icu_department_id,
                'patient_name' => $request->patient_name,
                'patient_age' => $request->patient_age,
                'patient_gender' => $request->patient_gender,
                'national_id' => $request->national_id,
                'insurance_number' => $request->insurance_number,
                'medical_condition' => $request->medical_condition,
                'diagnosis' => $request->diagnosis,
                'case_type' => $request->case_type,
                'urgency' => $request->urgency,
                'needs_ventilator' => $request->boolean('needs_ventilator'),
                'needs_oxygen' => $request->boolean('needs_oxygen'),
                'current_location' => $request->current_location,
                'needs_ambulance' => $request->boolean('needs_ambulance'),
                'contact_name' => $request->contact_name,
                'contact_phone' => $request->contact_phone,
                'relationship_to_patient' => $request->relationship_to_patient,
                'payment_method' => $request->payment_method,
                'insurance_provider' => $request->insurance_provider,
                'status' => IcuAdmissionRequest::STATUS_PENDING,
            ]);

            if ($request->hasFile('medical_reports')) {
                foreach ($request->file('medical_reports') as $file) {
                    $admission->addMedia($file)->toMediaCollection('medical_reports');
                }
            }

            return $admission;
        });

        // Send notification to admin about new ICU admission request
        $notification_data = [
            'id' => $order->id,
            'type' => 'new_icu_admission',
            'patient_id' => $order->patient_id,
            'request_number' => $order->request_number,
            'hospital_id' => $order->hospital_id,
            'urgency' => $order->urgency,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('new_icu_admission', $notification_data);
        }

        return redirect()->route('my-icu-requests')->withSuccess(__('intensive_care.request_save_success'));
    }

    public function myRequests()
    {
        $pageTitle = __('intensive_care.my_icu_requests');

        return view('frontend::my_icu_requests', compact('pageTitle'));
    }

    public function myRequests_index_data(Request $request)
    {
        $query = IcuAdmissionRequest::with(['hospital', 'icuDepartment'])
            ->where('patient_id', auth()->id());

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $perPage = $request->per_page ?? 10;
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'request_number' => $req->request_number,
                    'hospital_name' => $req->hospital ? $req->hospital->name : '-',
                    'case_type' => $req->case_type,
                    'case_type_label' => __('intensive_care.case_' . $req->case_type),
                    'urgency' => $req->urgency,
                    'urgency_label' => __('intensive_care.urgency_' . $req->urgency),
                    'status' => $req->status,
                    'status_label' => __('intensive_care.status_' . $req->status),
                    'created_at' => $req->created_at->format('d M Y'),
                ];
            }),
            'total' => $requests->total(),
            'last_page' => $requests->lastPage(),
        ]);
    }

    public function requestDetail($id)
    {
        $admission = IcuAdmissionRequest::with(['hospital', 'icuDepartment', 'respondedBy'])
            ->where('patient_id', auth()->id())
            ->findOrFail($id);

        $pageTitle = __('intensive_care.order_detail') . ' #' . $admission->request_number;

        return view('frontend::icu_request_detail', compact('admission', 'pageTitle'));
    }

    public function respond(Request $request, $id)
    {
        $admission = IcuAdmissionRequest::where('patient_id', auth()->id())->findOrFail($id);

        if ($admission->status !== IcuAdmissionRequest::STATUS_INFO_REQUESTED) {
            return redirect()->back()->withErrors(__('intensive_care.invalid_status_transition'));
        }

        $admission->update([
            'status' => IcuAdmissionRequest::STATUS_PENDING,
        ]);

        if ($request->hasFile('medical_reports')) {
            foreach ($request->file('medical_reports') as $file) {
                $admission->addMedia($file)->toMediaCollection('medical_reports');
            }
        }

        // Send notification about patient response
        $notification_data = [
            'id' => $admission->id,
            'type' => 'icu_patient_responded',
            'patient_id' => $admission->patient_id,
            'request_number' => $admission->request_number,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('icu_patient_responded', $notification_data);
        }

        return redirect()->route('my-icu-requests.detail', $admission->id)->withSuccess(__('intensive_care.request_save_success'));
    }

    public function cancel(Request $request, $id)
    {
        $admission = IcuAdmissionRequest::where('patient_id', auth()->id())->findOrFail($id);

        if (! $admission->canTransitionTo(IcuAdmissionRequest::STATUS_CANCELLED)) {
            return redirect()->back()->withErrors(__('intensive_care.cannot_cancel'));
        }

        $admission->update([
            'status' => IcuAdmissionRequest::STATUS_CANCELLED,
            'cancelled_by' => auth()->id(),
            'cancellation_reason' => $request->cancellation_reason ?? null,
        ]);

        // Send notification about request cancellation
        $notification_data = [
            'id' => $admission->id,
            'type' => 'icu_request_cancelled',
            'patient_id' => $admission->patient_id,
            'request_number' => $admission->request_number,
            'cancellation_reason' => $request->cancellation_reason ?? '',
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('icu_request_cancelled', $notification_data);
        }

        return redirect()->route('my-icu-requests')->withSuccess(__('intensive_care.request_cancel_success'));
    }
}
