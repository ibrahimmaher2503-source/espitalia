<?php

namespace Modules\RequestNurse\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;
use Modules\RequestNurse\Transformers\NurseRequestResource;

class NurseRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $query = NurseRequest::with(['patient', 'nurse', 'nurseProfile']);

        if (auth()->user()->hasRole('nurse')) {
            $query->where('nurse_id', auth()->id());
        } elseif (auth()->user()->hasRole('user')) {
            $query->where('patient_id', auth()->id());
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_description_ar', 'LIKE', "%$search%")
                    ->orWhere('service_description_en', 'LIKE', "%$search%")
                    ->orWhere('city', 'LIKE', "%$search%")
                    ->orWhereHas('patient', function ($q) use ($search) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    });
            });
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $data = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return NurseRequestResource::collection($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_description_en' => 'required_without:service_description_ar|nullable|string|max:2000',
            'service_description_ar' => 'required_without:service_description_en|nullable|string|max:2000',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1|max:24',
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
            'nurse_id' => 'nullable|exists:users,id',
            'address_line_2' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'patient_notes' => 'nullable|string|max:2000',
            'governorate_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
        ]);

        // Whitelist fields to prevent mass assignment of status/total_amount/payment_status
        $data = $request->only([
            'service_description_ar', 'service_description_en',
            'preferred_date', 'preferred_time', 'duration_hours',
            'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code',
            'latitude', 'longitude', 'contact_number', 'patient_notes',
            'nurse_id', 'governorate_id', 'city_id',
        ]);

        $data['patient_id'] = auth()->id();
        $data['request_date'] = now();
        $data['status'] = NurseRequest::STATUS_PENDING;

        if (! empty($data['nurse_id'])) {
            $nurseProfile = Nurse::where('nurse_id', $data['nurse_id'])->first();
            if ($nurseProfile) {
                $data['hourly_rate'] = $nurseProfile->hourly_rate;
                $data['total_amount'] = $nurseProfile->hourly_rate * $data['duration_hours'];
            }
        }

        // Apply offer/coupon discount if Offer module is active and amount is set
        if (! empty($data['total_amount']) && class_exists(\Modules\Offer\Services\OfferEligibilityService::class)) {
            try {
                $eligibility = app(\Modules\Offer\Services\OfferEligibilityService::class);
                $pricing = app(\Modules\Offer\Services\OfferPricingService::class);

                $eligible = $eligibility->getEligibleOffers(
                    $data['patient_id'], null, null, null, $data['total_amount'],
                    'app', null, $request->input('coupon_code')
                );

                if ($eligible->isNotEmpty()) {
                    $calc = $pricing->calculateDiscount($data['total_amount'], $eligible, [
                        'user_id' => $data['patient_id'],
                    ]);
                    if ($calc['discount_amount'] > 0) {
                        $data['offer_id'] = $calc['applied_offer_id'] ?? null;
                        $data['offer_discount'] = round($calc['discount_amount'], 2);
                        $data['offer_code'] = $calc['applied_offer_code'] ?? null;
                        $data['total_amount'] = round($calc['discounted_price'], 2);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Offer discount on nurse request failed: ' . $e->getMessage());
            }
        }

        $nurseRequest = NurseRequest::create($data);

        // Record offer usage
        if (! empty($data['offer_id']) && class_exists(\Modules\Offer\Services\OfferEligibilityService::class)) {
            try {
                app(\Modules\Offer\Services\OfferEligibilityService::class)->recordUsage(
                    $data['offer_id'],
                    $nurseRequest->patient_id,
                    $nurseRequest->id,
                    'nurse_request',
                    null,
                    $data['offer_discount']
                );
            } catch (\Throwable $e) {
                \Log::warning('Offer usage recording failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => __('request_nurse.request_save_success'),
            'data' => new NurseRequestResource($nurseRequest->load(['patient', 'nurse', 'nurseProfile'])),
            'status' => true,
        ], 200);
    }

    public function show($id)
    {
        $nurseRequest = NurseRequest::with(['patient', 'nurse', 'nurseProfile'])->findOrFail($id);

        if (auth()->user()->hasRole('user') && $nurseRequest->patient_id !== auth()->id()) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }

        if (auth()->user()->hasRole('nurse') && $nurseRequest->nurse_id !== auth()->id()) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }

        return response()->json([
            'data' => new NurseRequestResource($nurseRequest),
            'status' => true,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $nurseRequest = NurseRequest::findOrFail($id);

        if (auth()->user()->hasRole('user') && $nurseRequest->patient_id !== auth()->id()) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }

        if (auth()->user()->hasRole('user') && $nurseRequest->status !== NurseRequest::STATUS_PENDING) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }

        $nurseRequest->update($request->only([
            'service_description_ar', 'service_description_en',
            'preferred_date', 'preferred_time',
            'duration_hours', 'address_line_1', 'address_line_2',
            'city', 'state', 'country', 'postal_code',
            'contact_number', 'patient_notes',
        ]));

        return response()->json([
            'message' => __('request_nurse.request_update_success'),
            'data' => new NurseRequestResource($nurseRequest->load(['patient', 'nurse', 'nurseProfile'])),
            'status' => true,
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        $nurseRequest = NurseRequest::findOrFail($id);

        // Authorization: only the patient, assigned nurse, or admin can cancel
        if (auth()->user()->hasRole('user') && $nurseRequest->patient_id !== auth()->id()) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }
        if (auth()->user()->hasRole('nurse') && $nurseRequest->nurse_id !== auth()->id()) {
            return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 403);
        }

        if (in_array($nurseRequest->status, [NurseRequest::STATUS_COMPLETED, NurseRequest::STATUS_CANCELLED])) {
            return response()->json([
                'message' => __('request_nurse.cannot_cancel_completed'),
                'status' => false,
            ], 400);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $cancelledBy = 'admin';
        if (auth()->user()->hasRole('user')) {
            $cancelledBy = 'patient';
        } elseif (auth()->user()->hasRole('nurse')) {
            $cancelledBy = 'nurse';
        }

        $nurseRequest->update([
            'status' => NurseRequest::STATUS_CANCELLED,
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return response()->json([
            'message' => __('request_nurse.request_cancel_success'),
            'status' => true,
        ], 200);
    }
}
