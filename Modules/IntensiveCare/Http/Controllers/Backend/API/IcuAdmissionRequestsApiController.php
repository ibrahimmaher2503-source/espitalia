<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\IntensiveCare\Http\Requests\IcuAdmissionRequestFormRequest;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Transformers\IcuAdmissionRequestResource;

class IcuAdmissionRequestsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = IcuAdmissionRequest::with(['patient', 'hospital', 'icuDepartment']);

            $user = auth()->user();

            if ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            } elseif ($user->hasRole('vendor')) {
                $query->whereHas('hospital', function ($q) use ($user) {
                    $q->where('vendor_id', $user->id);
                });
            }
            // admin/demo_admin see all

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('hospital_id')) {
                $query->where('hospital_id', $request->hospital_id);
            }

            if ($request->filled('urgency')) {
                $query->where('urgency', $request->urgency);
            }

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return IcuAdmissionRequestResource::collection($requests)->additional([
                'status' => true,
                'message' => 'Admission request list',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function store(IcuAdmissionRequestFormRequest $request)
    {
        try {
            $user = auth()->user();

            $admissionRequest = DB::transaction(function () use ($request, $user) {
                $data = $request->validated();
                $data['patient_id'] = $user->id;
                $data['status'] = IcuAdmissionRequest::STATUS_PENDING;

                // Remove file fields from data
                unset($data['medical_reports']);

                $admissionRequest = IcuAdmissionRequest::create($data);

                // Handle media uploads
                if ($request->hasFile('medical_reports')) {
                    foreach ($request->file('medical_reports') as $file) {
                        $admissionRequest->addMedia($file)->toMediaCollection('medical_reports');
                    }
                }

                return $admissionRequest;
            });

            $admissionRequest->load(['patient', 'hospital', 'icuDepartment', 'media']);

            // Send notification
            $notification_data = [
                'id' => $admissionRequest->id,
                'type' => 'new_icu_admission_request',
                'patient_id' => $admissionRequest->patient_id,
                'request_number' => $admissionRequest->request_number,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('new_icu_admission_request', $notification_data);
            }

            return response()->json([
                'status' => true,
                'message' => __('intensive_care.request_save_success'),
                'data' => [
                    'id' => $admissionRequest->id,
                    'request_number' => $admissionRequest->request_number,
                    'status' => $admissionRequest->status,
                    'created_at' => $admissionRequest->created_at ? $admissionRequest->created_at->toISOString() : null,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function show($id)
    {
        try {
            $query = IcuAdmissionRequest::with(['patient', 'hospital', 'icuDepartment', 'respondedBy', 'media']);

            $user = auth()->user();
            if ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            } elseif ($user->hasRole('vendor')) {
                $query->whereHas('hospital', function ($q) use ($user) {
                    $q->where('vendor_id', $user->id);
                });
            }

            $admissionRequest = $query->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Admission request detail',
                'data' => new IcuAdmissionRequestResource($admissionRequest),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('intensive_care.request_not_found')], 404);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $request->validate([
                'cancellation_reason' => 'required|string|max:500',
            ]);

            $user = auth()->user();
            $admissionRequest = IcuAdmissionRequest::where('patient_id', $user->id)->findOrFail($id);

            if (! $admissionRequest->canTransitionTo(IcuAdmissionRequest::STATUS_CANCELLED)) {
                return response()->json([
                    'status' => false,
                    'message' => __('intensive_care.cannot_cancel'),
                ], 403);
            }

            $admissionRequest->update([
                'status' => IcuAdmissionRequest::STATUS_CANCELLED,
                'cancelled_by' => $user->id,
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            // Send notification
            $notification_data = [
                'id' => $admissionRequest->id,
                'type' => 'icu_admission_cancelled',
                'patient_id' => $admissionRequest->patient_id,
                'request_number' => $admissionRequest->request_number,
                'cancellation_reason' => $request->cancellation_reason,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('icu_admission_cancelled', $notification_data);
            }

            return response()->json([
                'status' => true,
                'message' => __('intensive_care.request_cancel_success'),
                'data' => [
                    'id' => $admissionRequest->id,
                    'status' => $admissionRequest->status,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to cancel this request',
            ], 403);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
