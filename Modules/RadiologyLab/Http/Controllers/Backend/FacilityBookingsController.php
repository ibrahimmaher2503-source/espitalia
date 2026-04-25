<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Models\FacilityBooking;
use Yajra\DataTables\DataTables;

class FacilityBookingsController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.booking_plural';
        $this->module_name = 'facility-bookings';
        $this->module_icon = 'ph ph-calendar-check';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_facility_bookings'])->only('index', 'index_data', 'show');
        $this->middleware(['permission:manage_facility_bookings'])->only('confirm', 'cancel', 'update_status', 'bulk_action');
        $this->middleware(['permission:manage_facility_bookings'])->only('destroy', 'action');
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
            'type' => $request->type,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.booking_plural');
        $assets = ['datatable'];

        // PF-05: single aggregate query
        $counts = FacilityBooking::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $stats = [
            'total' => array_sum($counts),
            'pending' => $counts[FacilityBooking::STATUS_PENDING] ?? 0,
            'confirmed' => $counts[FacilityBooking::STATUS_CONFIRMED] ?? 0,
            'completed' => $counts[FacilityBooking::STATUS_COMPLETED] ?? 0,
        ];

        return view('radiologylab::backend.facility-bookings.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'stats'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            $query = FacilityBooking::with(['patient', 'lab', 'radiologyCenter']);

            if ($request->has('filter')) {
                if (isset($request->filter['column_status']) && $request->filter['column_status'] !== '') {
                    $query->where('status', $request->filter['column_status']);
                }
                if (!empty($request->filter['type'])) {
                    $query->where('type', $request->filter['type']);
                }
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="facility-booking" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('booking_number', function ($data) {
                    return '<a href="' . route('backend.facility-bookings.show', $data->id) . '" class="fw-medium">' . e($data->booking_number) . '</a>';
                })
                ->editColumn('type', function ($data) {
                    return $data->type === 'lab'
                        ? '<span class="badge bg-soft-primary text-primary">' . __('radiology_lab.type_lab') . '</span>'
                        : '<span class="badge bg-soft-info text-info">' . __('radiology_lab.type_radiology') . '</span>';
                })
                ->addColumn('facility_name', function ($data) {
                    $facility = $data->facility;
                    return $facility ? e($facility->name) : '-';
                })
                ->addColumn('patient_display', function ($data) {
                    return e($data->patient_name ?: ($data->patient ? $data->patient->full_name : '-'));
                })
                ->editColumn('booking_date', function ($data) {
                    return $data->booking_date ? $data->booking_date->format('d M Y') : '-';
                })
                ->editColumn('booking_time', function ($data) {
                    return $data->booking_time ?: '-';
                })
                ->editColumn('status', function ($data) {
                    $colors = [
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'secondary',
                    ];
                    $color = $colors[$data->status] ?? 'secondary';
                    return '<span class="badge bg-soft-' . $color . ' text-' . $color . '">' . __('radiology_lab.status_' . $data->status) . '</span>';
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
                    $booking = $data;
                    return view('radiologylab::backend.facility-bookings.datatable.action_column', compact('booking', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'booking_number', 'type', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('FacilityBookings index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function show($id)
    {
        $booking = FacilityBooking::with(['patient', 'lab', 'radiologyCenter'])->findOrFail($id);
        $pageTitle = __('radiology_lab.booking_singular') . ' - ' . $booking->booking_number;

        return view('radiologylab::backend.facility-bookings.show', compact('pageTitle', 'booking'));
    }

    public function confirm(Request $request, $id)
    {
        $booking = FacilityBooking::findOrFail($id);

        if (!$booking->canTransitionTo(FacilityBooking::STATUS_CONFIRMED)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $booking->update([
            'status' => FacilityBooking::STATUS_CONFIRMED,
            'admin_notes' => $request->admin_notes ?? $booking->admin_notes,
        ]);

        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('facility_booking_confirmed', [
                'id' => $booking->id,
                'type' => 'facility_booking_confirmed',
                'patient_id' => $booking->patient_id,
                'booking_number' => $booking->booking_number,
            ]);
        }

        return response()->json(['status' => true, 'message' => __('radiology_lab.booking_confirmed')]);
    }

    public function cancel(Request $request, $id)
    {
        $booking = FacilityBooking::findOrFail($id);

        if (!$booking->canTransitionTo(FacilityBooking::STATUS_CANCELLED)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $booking->update([
            'status' => FacilityBooking::STATUS_CANCELLED,
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => auth()->id(),
        ]);

        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('facility_booking_cancelled', [
                'id' => $booking->id,
                'type' => 'facility_booking_cancelled',
                'patient_id' => $booking->patient_id,
                'booking_number' => $booking->booking_number,
            ]);
        }

        return response()->json(['status' => true, 'message' => __('radiology_lab.booking_cancelled')]);
    }

    public function update_status(Request $request, $id)
    {
        $booking = FacilityBooking::findOrFail($id);
        $newStatus = $request->status;

        if (!$booking->canTransitionTo($newStatus)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === FacilityBooking::STATUS_CANCELLED) {
            $updateData['cancelled_by'] = auth()->id();
            $updateData['cancellation_reason'] = $request->cancellation_reason ?? null;
        }

        $booking->update($updateData);

        $statusMessages = [
            FacilityBooking::STATUS_COMPLETED => __('radiology_lab.booking_completed'),
            FacilityBooking::STATUS_NO_SHOW => __('radiology_lab.booking_no_show'),
            FacilityBooking::STATUS_CANCELLED => __('radiology_lab.booking_cancelled'),
            FacilityBooking::STATUS_CONFIRMED => __('radiology_lab.booking_confirmed'),
        ];

        return response()->json([
            'status' => true,
            'message' => $statusMessages[$newStatus] ?? __('radiology_lab.status_update_success'),
        ]);
    }

    public function destroy($id)
    {
        $booking = FacilityBooking::find($id);
        if ($booking) {
            $booking->delete();
            $msg = __('radiology_lab.booking_cancelled');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.booking_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $booking ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $booking = FacilityBooking::withTrashed()->where('id', $id)->first();

        if (!$booking) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.booking_singular')]);

        if ($request->type === 'restore') {
            $booking->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $booking->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bulk_action(Request $request)
    {
        $bookingStatuses = ['pending', 'confirmed', 'completed', 'no_show'];

        $request->validate([
            'rowIds' => 'required|string',
            'action_type' => 'required|string|in:change-status,delete,restore,permanently-delete',
            // FN-20: bulk cancellation not allowed — per-row only, so we can store a reason
            'status' => 'required_if:action_type,change-status|in:' . implode(',', $bookingStatuses),
        ]);

        $ids = array_filter(array_map('intval', explode(',', $request->rowIds)));
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $newStatus = $request->status;
                foreach ($ids as $id) {
                    $booking = FacilityBooking::find($id);
                    if ($booking && $booking->canTransitionTo($newStatus)) {
                        $booking->update(['status' => $newStatus]);
                    }
                }
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                FacilityBooking::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                FacilityBooking::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                FacilityBooking::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
