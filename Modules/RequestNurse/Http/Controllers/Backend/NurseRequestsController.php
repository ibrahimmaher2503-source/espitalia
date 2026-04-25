<?php

namespace Modules\RequestNurse\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;
use Yajra\DataTables\DataTables;

class NurseRequestsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'request_nurse.plural_title';
        $this->module_name = 'nurse-requests';
        $this->module_icon = 'ph ph-first-aid';

        // Permission middleware
        $this->middleware('can:view_request_nurse')->only(['index', 'index_data', 'show']);
        $this->middleware('can:add_request_nurse')->only(['create', 'store']);
        $this->middleware('can:edit_request_nurse')->only(['edit', 'update', 'update_status', 'assign_nurse']);
        $this->middleware('can:delete_request_nurse')->only(['destroy', 'bulk_action', 'action']);

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('request_nurse.plural_title');
        $assets = ['datatable'];
        $nurses = Nurse::with('user')->active()->get();

        $stats = [
            'total' => NurseRequest::count(),
            'pending' => NurseRequest::where('status', NurseRequest::STATUS_PENDING)->count(),
            'in_progress' => NurseRequest::where('status', NurseRequest::STATUS_IN_PROGRESS)->count(),
            'completed' => NurseRequest::where('status', NurseRequest::STATUS_COMPLETED)->count(),
        ];

        return view('requestnurse::backend.requests.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'nurses', 'stats'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = NurseRequest::with(['patient', 'nurse']);

            // Role-based filtering
            if (auth()->user()->hasRole('nurse')) {
                $query->where('nurse_id', auth()->id());
            }

            // Filters
            if ($request->has('filter')) {
                $filter = $request->filter;
                if (isset($filter['column_status']) && $filter['column_status'] !== '') {
                    $query->where('status', $filter['column_status']);
                }
                if (isset($filter['nurse_id']) && $filter['nurse_id'] !== '') {
                    $query->where('nurse_id', $filter['nurse_id']);
                }
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$data->id.'" name="datatable_ids[]" value="'.$data->id.'" data-type="nurse-request" onclick="dataTableRowCheck('.$data->id.',this)">';
                })
                ->addColumn('patient_name', function ($data) {
                    return $data->patient ? $data->patient->first_name.' '.$data->patient->last_name : '-';
                })
                ->filterColumn('patient_name', function ($query, $keyword) {
                    $query->whereHas('patient', function ($q) use ($keyword) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$keyword%"]);
                    });
                })
                ->addColumn('nurse_name', function ($data) {
                    if ($data->nurse) {
                        return $data->nurse->first_name.' '.$data->nurse->last_name;
                    }

                    return '<span class="badge bg-secondary">'.__('request_nurse.lbl_unassigned').'</span>';
                })
                ->filterColumn('nurse_name', function ($query, $keyword) {
                    $query->whereHas('nurse', function ($q) use ($keyword) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$keyword%"]);
                    });
                })
                ->editColumn('preferred_date', function ($data) {
                    $date = $data->preferred_date ? $data->preferred_date->format('d M Y') : '-';
                    $time = $data->preferred_time ?? '';

                    return $date.($time ? ' '.$time : '');
                })
                ->addColumn('city', function ($data) {
                    return $data->city ?: '-';
                })
                ->editColumn('status', function ($data) {
                    return $data->status_badge;
                })
                ->addColumn('payment_badge', function ($data) {
                    return $data->payment_status
                        ? '<span class="badge bg-success">'.__('request_nurse.status_paid').'</span>'
                        : '<span class="badge bg-warning text-dark">'.__('request_nurse.status_unpaid').'</span>';
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
                    $nurseRequest = $data;

                    return view('requestnurse::backend.requests.datatable.action_column', compact('nurseRequest', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'nurse_name', 'status', 'payment_badge', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $nurseRequest = NurseRequest::with(['patient', 'nurse', 'nurseProfile'])->findOrFail($id);
        $nurses = Nurse::with('user')->available()->get();
        $pageTitle = __('request_nurse.nurse_request_detail');

        return view('requestnurse::backend.requests.show', compact('nurseRequest', 'nurses', 'pageTitle'));
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $nurseRequest = NurseRequest::find($id);
        $nurses = Nurse::with('user')->active()->get();
        $patients = User::whereHas('roles', function ($q) {
            $q->where('name', 'user');
        })->select('id', 'first_name', 'last_name', 'email')->get();

        $pageTitle = trans('messages.add_button_form', ['form' => trans('request_nurse.singular_title')]);

        if ($nurseRequest) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('request_nurse.singular_title')]);
        } else {
            $nurseRequest = new NurseRequest;
        }

        return view('requestnurse::backend.requests.create', compact('pageTitle', 'nurseRequest', 'nurses', 'patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'service_description_ar' => 'nullable|string|max:2000',
            'service_description_en' => 'nullable|string|max:2000',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1|max:24',
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
        ]);

        $data = $request->only(['nurse_id', 'patient_id', 'service_description_ar', 'service_description_en', 'preferred_date', 'preferred_time', 'duration_hours', 'address_line_1', 'address_line_2', 'city', 'state', 'country', 'postal_code', 'contact_number', 'patient_notes', 'admin_notes']);

        $nurseRequestId = $request->id;

        if ($nurseRequestId) {
            $nurseRequest = NurseRequest::findOrFail($nurseRequestId);
            $nurseRequest->update($data);
            $message = __('request_nurse.request_update_success');
        } else {
            $data['request_date'] = now();
            $data['status'] = NurseRequest::STATUS_PENDING;

            // Calculate total amount if nurse is assigned
            if (! empty($data['nurse_id'])) {
                $nurseProfile = Nurse::where('nurse_id', $data['nurse_id'])->first();
                if ($nurseProfile) {
                    $data['total_amount'] = $nurseProfile->hourly_rate * $data['duration_hours'];
                }
            }

            $nurseRequest = NurseRequest::create($data);
            $message = __('request_nurse.request_save_success');

            // Send notification to admin about new request
            $notification_data = [
                'id' => $nurseRequest->id,
                'type' => 'new_nurse_request',
                'patient_id' => $nurseRequest->patient_id,
                'service_description' => $nurseRequest->service_description,
                'preferred_date' => $nurseRequest->preferred_date,
            ];
            sendNotificationOnBookingUpdate('new_nurse_request', $notification_data);
        }

        return redirect()->route('backend.nurse-requests.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $nurseRequest = NurseRequest::find($id);
        if ($nurseRequest) {
            $nurseRequest->delete();
            $msg = __('request_nurse.request_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('request_nurse.singular_title')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $nurseRequest ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $nurseRequest = NurseRequest::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('request_nurse.singular_title')]);

        if ($request->type == 'restore') {
            $nurseRequest->restore();
            $msg = __('request_nurse.nurse_request_restore');
        }

        if ($request->type === 'forcedelete') {
            $nurseRequest->forceDelete();
            $msg = __('request_nurse.request_force_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function assign_nurse(Request $request, $id)
    {
        $request->validate([
            'nurse_id' => 'required|exists:users,id',
        ]);

        $nurseRequest = NurseRequest::findOrFail($id);

        $nurseProfile = Nurse::where('nurse_id', $request->nurse_id)->first();
        if (! $nurseProfile) {
            return response()->json(['status' => false, 'message' => __('request_nurse.no_nurses_available')]);
        }

        $nurseRequest->update([
            'nurse_id' => $request->nurse_id,
            'status' => NurseRequest::STATUS_CONFIRMED,
            'total_amount' => $nurseProfile->hourly_rate * $nurseRequest->duration_hours,
        ]);

        // Send notification to patient & nurse about assignment
        $notification_data = [
            'id' => $nurseRequest->id,
            'type' => 'nurse_assigned',
            'patient_id' => $nurseRequest->patient_id,
            'nurse_id' => $request->nurse_id,
            'preferred_date' => $nurseRequest->preferred_date,
        ];
        sendNotificationOnBookingUpdate('nurse_assigned', $notification_data);

        $message = __('request_nurse.nurse_assign_success');

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function update_status(Request $request, $id)
    {
        $nurseRequest = NurseRequest::findOrFail($id);

        $newStatus = $request->status;

        // Validate status transitions
        $allowedTransitions = [
            NurseRequest::STATUS_PENDING => [NurseRequest::STATUS_CONFIRMED, NurseRequest::STATUS_CANCELLED],
            NurseRequest::STATUS_CONFIRMED => [NurseRequest::STATUS_IN_PROGRESS, NurseRequest::STATUS_CANCELLED],
            NurseRequest::STATUS_IN_PROGRESS => [NurseRequest::STATUS_COMPLETED, NurseRequest::STATUS_CANCELLED],
            NurseRequest::STATUS_COMPLETED => [],
            NurseRequest::STATUS_CANCELLED => [],
        ];

        if (! in_array($newStatus, $allowedTransitions[$nurseRequest->status] ?? [])) {
            return response()->json([
                'status' => false,
                'message' => __('request_nurse.invalid_status_transition'),
            ], 422);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === NurseRequest::STATUS_CANCELLED) {
            $updateData['cancelled_by'] = auth()->user()->hasRole('nurse') ? 'nurse' : 'admin';
            $updateData['cancellation_reason'] = $request->cancellation_reason ?? null;
        }

        $nurseRequest->update($updateData);

        // Send notification on status change
        $notificationType = match ($newStatus) {
            NurseRequest::STATUS_CONFIRMED => 'nurse_request_confirmed',
            NurseRequest::STATUS_IN_PROGRESS => 'nurse_request_in_progress',
            NurseRequest::STATUS_COMPLETED => 'nurse_request_completed',
            NurseRequest::STATUS_CANCELLED => 'nurse_request_cancelled_by_admin',
            default => null,
        };

        if ($notificationType) {
            $notification_data = [
                'id' => $nurseRequest->id,
                'type' => $notificationType,
                'patient_id' => $nurseRequest->patient_id,
                'nurse_id' => $nurseRequest->nurse_id,
                'status' => $newStatus,
                'preferred_date' => $nurseRequest->preferred_date,
            ];
            sendNotificationOnBookingUpdate($notificationType, $notification_data);
        }

        return response()->json(['status' => true, 'message' => __('request_nurse.status_update_success')]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                // Validate transitions: only allow forward changes per state machine
                $allowedTransitions = [
                    NurseRequest::STATUS_PENDING => [NurseRequest::STATUS_CONFIRMED, NurseRequest::STATUS_CANCELLED],
                    NurseRequest::STATUS_CONFIRMED => [NurseRequest::STATUS_IN_PROGRESS, NurseRequest::STATUS_CANCELLED],
                    NurseRequest::STATUS_IN_PROGRESS => [NurseRequest::STATUS_COMPLETED, NurseRequest::STATUS_CANCELLED],
                ];

                $newStatus = $request->status;
                $updated = 0;
                foreach (NurseRequest::whereIn('id', $ids)->get() as $nr) {
                    $allowed = $allowedTransitions[$nr->status] ?? [];
                    if (in_array($newStatus, $allowed)) {
                        $updateData = ['status' => $newStatus];
                        if ($newStatus === NurseRequest::STATUS_CANCELLED) {
                            $updateData['cancelled_by'] = 'admin';
                        }
                        $nr->update($updateData);
                        $updated++;
                    }
                }
                $message = __('messages.bulk_service_update')." ({$updated} updated)";
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                NurseRequest::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                NurseRequest::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                NurseRequest::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
