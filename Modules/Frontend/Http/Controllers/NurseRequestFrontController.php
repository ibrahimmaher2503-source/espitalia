<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;

class NurseRequestFrontController extends Controller
{
    public function nursesList()
    {
        $pageTitle = __('request_nurse.available_nurses');
        $specializations = \Modules\Specialization\Models\Specialization::active()->orderBy('name_en')->get(['id', 'name_ar', 'name_en']);

        return view('frontend::available_nurses', compact('pageTitle', 'specializations'));
    }

    public function nurse_index_data(Request $request)
    {
        $query = Nurse::with(['user', 'specializations'])->available();

        if ($request->has('specialization_ids') && is_array($request->specialization_ids)) {
            $specIds = $request->specialization_ids;
            $query->where(function ($q) use ($specIds) {
                $q->whereHas('specializations', function ($sq) use ($specIds) {
                    $sq->whereIn('specializations.id', $specIds);
                });
            });
        } elseif ($request->has('specialization') && $request->specialization !== '') {
            $spec = $request->specialization;
            $query->where(function ($q) use ($spec) {
                $q->where('specialization_ar', 'LIKE', "%{$spec}%")
                    ->orWhere('specialization_en', 'LIKE', "%{$spec}%");
            });
        }

        if ($request->has('availability_status') && $request->availability_status !== '') {
            $query->where('availability_status', $request->availability_status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('specialization', 'LIKE', "%$search%")
                  ->orWhere('service_area', 'LIKE', "%$search%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                  });
            });
        }

        $perPage = $request->per_page ?? 12;
        $nurses = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $nurses->map(function ($nurse) {
                return [
                    'id' => $nurse->nurse_id,
                    'name' => $nurse->user ? $nurse->user->first_name . ' ' . $nurse->user->last_name : '',
                    'specialization' => $nurse->specialization ?? 'General',
                    'experience' => $nurse->experience ?? '-',
                    'hourly_rate' => $nurse->hourly_rate,
                    'service_area' => $nurse->service_area ?? '-',
                    'about' => $nurse->about,
                    'availability_status' => $nurse->availability_status,
                    'specializations' => $nurse->specializations->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'slug' => $s->slug,
                    ]),
                    'profile_image' => $nurse->getFirstMediaUrl('profile_image') ?: asset('img/default-avatar.png'),
                ];
            }),
            'total' => $nurses->total(),
            'last_page' => $nurses->lastPage(),
        ]);
    }

    public function nurseDetails($id)
    {
        $nurse = Nurse::with('user')->where('nurse_id', $id)->firstOrFail();
        $pageTitle = ($nurse->user->first_name ?? '') . ' ' . ($nurse->user->last_name ?? '');
        return view('frontend::nurse_detail', compact('nurse', 'pageTitle'));
    }

    public function requestForm(Request $request)
    {
        $pageTitle = __('request_nurse.request_a_nurse');
        $nurses = Nurse::with('user')->available()->get();
        $selectedNurseId = $request->nurse_id ?? null;
        return view('frontend::request_nurse_form', compact('pageTitle', 'nurses', 'selectedNurseId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_description' => 'required|string|max:1000',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1|max:24',
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
        ]);

        $data = $request->only(['nurse_id', 'service_description', 'preferred_date', 'preferred_time', 'duration_hours', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code', 'contact_number', 'patient_notes']);
        $data['patient_id'] = auth()->id();
        $data['request_date'] = now();
        $data['status'] = NurseRequest::STATUS_PENDING;

        if (!empty($data['nurse_id'])) {
            $nurseProfile = Nurse::where('nurse_id', $data['nurse_id'])->first();
            if ($nurseProfile) {
                $data['total_amount'] = $nurseProfile->hourly_rate * $data['duration_hours'];
            }
        }

        $nurseRequest = NurseRequest::create($data);

        // Send notification to admin about new request
        $notification_data = [
            'id' => $nurseRequest->id,
            'type' => 'new_nurse_request',
            'patient_id' => $nurseRequest->patient_id,
            'service_description' => $nurseRequest->service_description,
            'preferred_date' => $nurseRequest->preferred_date,
        ];
        sendNotificationOnBookingUpdate('new_nurse_request', $notification_data);

        return redirect()->route('my-nurse-requests')->withSuccess(__('request_nurse.request_save_success'));
    }

    public function myRequests()
    {
        $pageTitle = __('request_nurse.my_nurse_requests');
        return view('frontend::my_nurse_requests', compact('pageTitle'));
    }

    public function myRequests_index_data(Request $request)
    {
        $query = NurseRequest::with(['nurse', 'nurseProfile'])
            ->where('patient_id', auth()->id());

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $perPage = $request->per_page ?? 10;
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $counts = [
            'all' => NurseRequest::where('patient_id', auth()->id())->count(),
            'pending' => NurseRequest::where('patient_id', auth()->id())->where('status', 'pending')->count(),
            'confirmed' => NurseRequest::where('patient_id', auth()->id())->where('status', 'confirmed')->count(),
            'completed' => NurseRequest::where('patient_id', auth()->id())->where('status', 'completed')->count(),
            'cancelled' => NurseRequest::where('patient_id', auth()->id())->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'data' => $requests->map(function ($req) {
                return [
                    'id' => $req->id,
                    'service_description' => \Str::limit($req->service_description, 80),
                    'preferred_date' => $req->preferred_date ? $req->preferred_date->format('d M Y') : '-',
                    'preferred_time' => $req->preferred_time ?? '-',
                    'duration_hours' => $req->duration_hours,
                    'city' => $req->city,
                    'status' => $req->status,
                    'status_badge' => $req->status_badge,
                    'nurse_name' => $req->nurse ? $req->nurse->first_name . ' ' . $req->nurse->last_name : null,
                    'total_amount' => number_format($req->total_amount, 2),
                    'created_at' => $req->created_at->format('d M Y'),
                ];
            }),
            'counts' => $counts,
            'total' => $requests->total(),
            'last_page' => $requests->lastPage(),
        ]);
    }

    public function requestDetail($id)
    {
        $nurseRequest = NurseRequest::with(['nurse', 'nurseProfile'])
            ->where('patient_id', auth()->id())
            ->findOrFail($id);

        $pageTitle = __('request_nurse.nurse_request_detail') . ' #' . $nurseRequest->id;
        return view('frontend::nurse_request_detail', compact('nurseRequest', 'pageTitle'));
    }

    public function cancel(Request $request, $id)
    {
        $nurseRequest = NurseRequest::where('patient_id', auth()->id())->findOrFail($id);

        if (in_array($nurseRequest->status, [NurseRequest::STATUS_COMPLETED, NurseRequest::STATUS_CANCELLED])) {
            return redirect()->back()->withErrors(__('request_nurse.cannot_cancel_completed'));
        }

        $nurseRequest->update([
            'status' => NurseRequest::STATUS_CANCELLED,
            'cancelled_by' => 'patient',
            'cancellation_reason' => $request->cancellation_reason ?? null,
        ]);

        // Send notification to admin + nurse (if assigned) about patient cancellation
        $notification_data = [
            'id' => $nurseRequest->id,
            'type' => 'nurse_request_cancelled',
            'patient_id' => $nurseRequest->patient_id,
            'nurse_id' => $nurseRequest->nurse_id,
            'cancellation_reason' => $request->cancellation_reason ?? '',
            'preferred_date' => $nurseRequest->preferred_date,
        ];
        sendNotificationOnBookingUpdate('nurse_request_cancelled', $notification_data);

        return redirect()->route('my-nurse-requests')->withSuccess(__('request_nurse.request_cancel_success'));
    }
}
