<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Services\IcuCommissionService;
use Yajra\DataTables\DataTables;

class IcuAdmissionRequestsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'intensive_care.request_plural';
        $this->module_name = 'icu-admission-requests';
        $this->module_icon = 'ph ph-clipboard-text';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_icu_admission_requests'])->only('index', 'index_data', 'show');
        $this->middleware(['permission:edit_icu_admission_requests'])->only('update_status', 'respond');
        $this->middleware(['permission:delete_icu_admission_requests'])->only('destroy', 'action', 'bulk_action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
            'urgency' => $request->urgency,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('intensive_care.request_plural');
        $assets = ['datatable'];

        $statuses = IcuAdmissionRequest::STATUSES;
        $urgencyLevels = IcuAdmissionRequest::URGENCY_LEVELS;

        // Build stats query with coordinator filter
        $statsQuery = IcuAdmissionRequest::query();
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::where('vendor_id', auth()->id())->first();
            if ($hospital) {
                $statsQuery->where('hospital_id', $hospital->id);
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', IcuAdmissionRequest::STATUS_PENDING)->count(),
            'accepted' => (clone $statsQuery)->where('status', IcuAdmissionRequest::STATUS_ACCEPTED)->count(),
            'rejected' => (clone $statsQuery)->where('status', IcuAdmissionRequest::STATUS_REJECTED)->count(),
        ];

        return view('intensivecare::backend.requests.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'statuses', 'urgencyLevels', 'stats'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = IcuAdmissionRequest::with(['hospital', 'icuDepartment', 'patient']);

            // Coordinator filter
            if (auth()->user()->hasRole('hospital_coordinator')) {
                $hospital = Hospital::where('vendor_id', auth()->id())->first();
                if ($hospital) {
                    $query->where('hospital_id', $hospital->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            // Filters
            if ($request->has('filter')) {
                $filter = $request->filter;
                if (isset($filter['column_status']) && $filter['column_status'] !== '') {
                    $query->where('status', $filter['column_status']);
                }
                if (isset($filter['urgency']) && $filter['urgency'] !== '') {
                    $query->where('urgency', $filter['urgency']);
                }
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="icu-admission-request" onclick="dataTableRowCheck('.$data->id.',this)">';
                })
                ->editColumn('request_number', function ($data) {
                    return '<a href="'.route('backend.icu-admission-requests.show', $data->id).'">'.$data->request_number.'</a>';
                })
                ->editColumn('patient_name', function ($data) {
                    return e($data->patient_name ?? '-');
                })
                ->addColumn('hospital_name', function ($data) {
                    return $data->hospital ? e($data->hospital->name) : '-';
                })
                ->filterColumn('hospital_name', function ($query, $keyword) {
                    $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);
                    $query->whereHas('hospital', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%$keyword%");
                    });
                })
                ->addColumn('medical_condition', function ($data) {
                    $text = $data->medical_condition;

                    return e(mb_strlen($text) > 60 ? mb_substr($text, 0, 60).'...' : $text);
                })
                ->editColumn('case_type', function ($data) {
                    return __('intensive_care.case_'.$data->case_type);
                })
                ->addColumn('urgency_badge', function ($data) {
                    return $data->urgency_badge;
                })
                ->editColumn('status', function ($data) {
                    return $data->status_badge;
                })
                ->editColumn('created_at', function ($data) {
                    return $data->created_at ? $data->created_at->format('d M Y') : '-';
                })
                ->editColumn('updated_at', function ($data) {
                    $diff = Carbon::now()->diffInHours($data->updated_at);
                    if ($diff < 25) {
                        return $data->updated_at->diffForHumans();
                    } else {
                        return $data->updated_at->isoFormat('llll');
                    }
                })
                ->addColumn('action', function ($data) use ($module_name) {
                    $admissionRequest = $data;

                    return view('intensivecare::backend.requests.datatable.action_column', compact('admissionRequest', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'request_number', 'urgency_badge', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function show($id)
    {
        $query = IcuAdmissionRequest::with(['hospital', 'icuDepartment', 'patient', 'respondedBy']);

        // Coordinator filter
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::where('vendor_id', auth()->id())->first();
            if ($hospital) {
                $query->where('hospital_id', $hospital->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $admissionRequest = $query->findOrFail($id);
        $allowedTransitions = IcuAdmissionRequest::ALLOWED_TRANSITIONS[$admissionRequest->status] ?? [];
        $pageTitle = __('intensive_care.request_detail');

        return view('intensivecare::backend.requests.show', compact('admissionRequest', 'allowedTransitions', 'pageTitle'));
    }

    public function update_status(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);

        $query = IcuAdmissionRequest::query();

        // Coordinator filter
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::where('vendor_id', auth()->id())->first();
            if ($hospital) {
                $query->where('hospital_id', $hospital->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $admissionRequest = $query->findOrFail($id);
        $newStatus = $request->status;

        if (! $admissionRequest->canTransitionTo($newStatus)) {
            return response()->json([
                'status' => false,
                'message' => __('intensive_care.invalid_status_transition'),
            ], 422);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === IcuAdmissionRequest::STATUS_CANCELLED) {
            $updateData['cancelled_by'] = auth()->id();
            $updateData['cancellation_reason'] = $request->cancellation_reason ?? null;
        }

        $admissionRequest->update($updateData);

        // Commission handling
        if ($newStatus === IcuAdmissionRequest::STATUS_ACCEPTED) {
            (new IcuCommissionService)->calculateCommission($admissionRequest);
        } elseif ($newStatus === IcuAdmissionRequest::STATUS_CANCELLED) {
            (new IcuCommissionService)->reverseCommission($admissionRequest);
        }

        // Notification
        $notification_data = [
            'id' => $admissionRequest->id,
            'type' => 'icu_admission_status_update',
            'patient_id' => $admissionRequest->patient_id,
            'request_number' => $admissionRequest->request_number,
            'status' => $newStatus,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('icu_admission_status_update', $notification_data);
        }

        return response()->json(['status' => true, 'message' => __('intensive_care.status_update_success')]);
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,reject,request_info',
            'admin_notes_ar' => 'nullable|string|max:2000',
            'admin_notes_en' => 'nullable|string|max:2000',
        ]);

        $query = IcuAdmissionRequest::query();

        // Coordinator filter
        if (auth()->user()->hasRole('hospital_coordinator')) {
            $hospital = Hospital::where('vendor_id', auth()->id())->first();
            if ($hospital) {
                $query->where('hospital_id', $hospital->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $admissionRequest = $query->findOrFail($id);
        $action = $request->action;

        $statusMap = [
            'accept' => IcuAdmissionRequest::STATUS_ACCEPTED,
            'reject' => IcuAdmissionRequest::STATUS_REJECTED,
            'request_info' => IcuAdmissionRequest::STATUS_INFO_REQUESTED,
        ];

        $newStatus = $statusMap[$action] ?? null;

        if (! $newStatus || ! $admissionRequest->canTransitionTo($newStatus)) {
            return response()->json([
                'status' => false,
                'message' => __('intensive_care.invalid_status_transition'),
            ]);
        }

        $updateData = [
            'status' => $newStatus,
            'admin_notes_ar' => $request->admin_notes_ar,
            'admin_notes_en' => $request->admin_notes_en,
            'response_date' => now(),
            'responded_by' => auth()->id(),
        ];

        if ($action === 'reject') {
            $updateData['rejection_reason_ar'] = $request->rejection_reason_ar;
            $updateData['rejection_reason_en'] = $request->rejection_reason_en;
        }

        $admissionRequest->update($updateData);

        // Commission handling
        if ($newStatus === IcuAdmissionRequest::STATUS_ACCEPTED) {
            (new IcuCommissionService)->calculateCommission($admissionRequest);
        }

        // Notification
        $notificationType = match ($newStatus) {
            IcuAdmissionRequest::STATUS_ACCEPTED => 'icu_admission_accepted',
            IcuAdmissionRequest::STATUS_REJECTED => 'icu_admission_rejected',
            IcuAdmissionRequest::STATUS_INFO_REQUESTED => 'icu_admission_info_requested',
            default => null,
        };

        if ($notificationType) {
            $notification_data = [
                'id' => $admissionRequest->id,
                'type' => $notificationType,
                'patient_id' => $admissionRequest->patient_id,
                'request_number' => $admissionRequest->request_number,
                'status' => $newStatus,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate($notificationType, $notification_data);
            }
        }

        return response()->json(['status' => true, 'message' => __('intensive_care.status_update_success')]);
    }

    public function destroy($id)
    {
        $admissionRequest = IcuAdmissionRequest::find($id);
        if ($admissionRequest) {
            $admissionRequest->delete();
            $msg = __('intensive_care.request_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('intensive_care.request_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $admissionRequest ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $admissionRequest = IcuAdmissionRequest::withTrashed()->where('id', $id)->first();

        if (! $admissionRequest) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('intensive_care.request_singular')]);

        if ($request->type === 'restore') {
            $admissionRequest->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $admissionRequest->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'rowIds' => 'required|string',
            'type' => 'required|string',
        ]);

        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                IcuAdmissionRequest::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                IcuAdmissionRequest::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                IcuAdmissionRequest::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
