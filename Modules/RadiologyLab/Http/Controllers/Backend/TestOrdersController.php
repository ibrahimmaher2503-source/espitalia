<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Http\Requests\TestOrderFormRequest;
use Modules\RadiologyLab\Http\Requests\TestResultFormRequest;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestReport;
use Modules\RadiologyLab\Models\TestResultHistory;
use Modules\Offer\Services\OfferEligibilityService;
use Modules\Offer\Services\OfferPricingService;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class TestOrdersController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'radiology_lab.order_plural';
        $this->module_name = 'test-orders';
        $this->module_icon = 'ph ph-clipboard-text';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);

        $this->middleware(['permission:view_test_orders'])->only('index', 'index_data', 'show');
        $this->middleware(['permission:add_test_orders'])->only('create', 'store');
        $this->middleware(['permission:edit_test_orders'])->only('update_status', 'assign_technician', 'update_admin_notes');
        $this->middleware(['permission:delete_test_orders'])->only('destroy', 'action');
        $this->middleware(['permission:manage_test_results'])->only('enter_results', 'upload_report', 'generate_report', 'download_report');
        // bulk_action gated inline — supports both edit (change-status) and delete actions
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
            'priority' => $request->priority,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('radiology_lab.order_plural');
        $assets = ['datatable'];

        $statuses = TestOrder::STATUSES;
        $priorities = TestOrder::PRIORITIES;

        // PF-04: single aggregate query instead of 4 separate COUNTs
        $statsQuery = TestOrder::query();
        if (auth()->user()->hasRole('lab_technician')) {
            $statsQuery->where('lab_technician_id', auth()->id());
        } elseif (auth()->user()->hasRole('doctor')) {
            $statsQuery->where('doctor_id', auth()->id());
        }

        $counts = $statsQuery->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->toArray();
        $stats = [
            'total' => array_sum($counts),
            'pending' => $counts[TestOrder::STATUS_PENDING] ?? 0,
            'processing' => $counts[TestOrder::STATUS_PROCESSING] ?? 0,
            'completed' => $counts[TestOrder::STATUS_COMPLETED] ?? 0,
        ];

        return view('radiologylab::backend.orders.index', compact('pageTitle', 'module_name', 'assets', 'filter', 'statuses', 'priorities', 'stats'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $module_name = $this->module_name;
            // PF-03: withCount instead of loading all items just to count
            $query = TestOrder::with(['patient', 'labTechnician'])->withCount('items');

            // Role-based filtering
            if (auth()->user()->hasRole('lab_technician')) {
                $query->where('lab_technician_id', auth()->id());
            } elseif (auth()->user()->hasRole('doctor')) {
                $query->where('doctor_id', auth()->id());
            }

            // Filters
            if ($request->has('filter')) {
                $filter = $request->filter;
                if (isset($filter['column_status']) && $filter['column_status'] !== '') {
                    $query->where('status', $filter['column_status']);
                }
                if (isset($filter['priority']) && $filter['priority'] !== '') {
                    $query->where('priority', $filter['priority']);
                }
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="test-order" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('order_number', function ($data) {
                    return '<a href="' . route('backend.test-orders.show', $data->id) . '">' . $data->order_number . '</a>';
                })
                ->addColumn('patient_name', function ($data) {
                    return $data->patient ? $data->patient->first_name . ' ' . $data->patient->last_name : '-';
                })
                ->filterColumn('patient_name', function ($query, $keyword) {
                    // PF-07: index-friendly — match first_name OR last_name, avoid CONCAT
                    $escaped = str_replace(['%', '_'], ['\%', '\_'], $keyword);
                    $query->whereHas('patient', function ($q) use ($escaped) {
                        $q->where('first_name', 'LIKE', "%{$escaped}%")
                          ->orWhere('last_name', 'LIKE', "%{$escaped}%");
                    });
                })
                ->addColumn('technician_name', function ($data) {
                    if ($data->labTechnician) {
                        return $data->labTechnician->first_name . ' ' . $data->labTechnician->last_name;
                    }
                    return '<span class="badge bg-secondary">' . __('radiology_lab.lbl_unassigned') . '</span>';
                })
                ->filterColumn('technician_name', function ($query, $keyword) {
                    $escaped = str_replace(['%', '_'], ['\%', '\_'], $keyword);
                    $query->whereHas('labTechnician', function ($q) use ($escaped) {
                        $q->where('first_name', 'LIKE', "%{$escaped}%")
                          ->orWhere('last_name', 'LIKE', "%{$escaped}%");
                    });
                })
                ->addColumn('items_count', function ($data) {
                    return '<span class="badge bg-primary">' . ($data->items_count ?? 0) . '</span>';
                })
                ->editColumn('order_date', function ($data) {
                    return $data->order_date ? $data->order_date->format('d M Y') : '-';
                })
                ->addColumn('priority_badge', function ($data) {
                    return $data->priority_badge;
                })
                ->editColumn('status', function ($data) {
                    return $data->status_badge;
                })
                ->addColumn('payment_badge', function ($data) {
                    return $data->payment_badge;
                })
                ->editColumn('final_amount', function ($data) {
                    return number_format($data->final_amount, 2);
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
                    $order = $data;
                    return view('radiologylab::backend.orders.datatable.action_column', compact('order', 'module_name'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'order_number', 'technician_name', 'items_count', 'priority_badge', 'status', 'payment_badge', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            \Log::error('TestOrders index_data error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = TestOrder::with('items.labTest', 'patient', 'doctor')->find($id);

        $pageTitle = trans('messages.add_button_form', ['form' => trans('radiology_lab.order_singular')]);

        if ($data) {
            $pageTitle = trans('messages.update_form_title', ['form' => trans('radiology_lab.order_singular')]);
        } else {
            $data = new TestOrder;
        }

        // PF-01: no longer preload every user; dropdowns use AJAX search via users_search()
        $categories = LabTestCategory::active()->ordered()->get();

        return view('radiologylab::backend.orders.create', compact('pageTitle', 'data', 'categories'));
    }

    /**
     * PF-01: paginated, role-filtered user search for Select2 AJAX mode.
     */
    public function users_search(Request $request)
    {
        $request->validate([
            'role' => 'required|in:user,doctor',
            'q' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $role = $request->input('role');
        $q = trim((string) $request->input('q', ''));

        $query = User::whereHas('roles', fn ($r) => $r->where('name', $role))
            ->select('id', 'first_name', 'last_name', 'email');

        if ($q !== '') {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function ($w) use ($escaped) {
                $w->where('first_name', 'LIKE', "%{$escaped}%")
                  ->orWhere('last_name', 'LIKE', "%{$escaped}%")
                  ->orWhere('email', 'LIKE', "%{$escaped}%");
            });
        }

        $paginator = $query->orderBy('first_name')->paginate(20);

        return response()->json([
            'results' => $paginator->map(fn ($u) => [
                'id' => $u->id,
                'text' => trim($u->first_name . ' ' . $u->last_name) . ($u->email ? ' (' . $u->email . ')' : ''),
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    /**
     * PF-02: paginated, filtered lab-test search for Select2 AJAX mode.
     */
    public function lab_tests_search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $q = trim((string) $request->input('q', ''));

        $query = LabTest::active()->with('category')->select('id', 'name', 'code', 'default_price', 'category_id');

        if ($q !== '') {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);
            $query->where(function ($w) use ($escaped) {
                $w->where('name', 'LIKE', "%{$escaped}%")
                  ->orWhere('code', 'LIKE', "%{$escaped}%");
            });
        }

        $paginator = $query->orderBy('name')->paginate(20);

        return response()->json([
            'results' => $paginator->map(fn ($t) => [
                'id' => $t->id,
                'text' => $t->name . ' (' . $t->code . ')',
                'price' => (float) $t->default_price,
                'category_id' => $t->category_id,
                'category_name' => $t->category?->name ?? '-',
            ])->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    public function store(TestOrderFormRequest $request)
    {
        $orderId = $request->id;

        if ($orderId) {
            abort_if(!auth()->user()->can('edit_test_orders'), 403);
        }

        $order = DB::transaction(function () use ($request, $orderId) {
            if ($orderId) {
                $order = TestOrder::findOrFail($orderId);

                $order->update([
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinical_notes' => $request->clinical_notes,
                    'priority' => $request->priority ?? 'routine',
                    'admin_notes' => $request->admin_notes,
                ]);

                // Only remove items that have no results entered
                $requestedTestIds = collect($request->items)->pluck('lab_test_id')->toArray();
                $order->items()->whereNull('result_value')->whereNotIn('lab_test_id', $requestedTestIds)->delete();

                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $labTest = LabTest::findOrFail($item['lab_test_id']);
                    $price = $labTest->default_price;

                    $existingItem = $order->items()->where('lab_test_id', $item['lab_test_id'])->first();
                    if (!$existingItem) {
                        TestOrderItem::create([
                            'test_order_id' => $order->id,
                            'lab_test_id' => $item['lab_test_id'],
                            'price' => $price,
                            'status' => TestOrderItem::STATUS_PENDING,
                        ]);
                    } else {
                        $price = $existingItem->price;
                    }

                    $totalAmount += $price;
                }

                $offerData = $this->applyOfferIfProvided($request, $totalAmount, $order->patient_id);
                $discountAmount = $offerData['discount_amount'];
                $order->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $totalAmount - $discountAmount,
                    'offer_id' => $offerData['offer_id'],
                    'offer_discount' => $offerData['discount_amount'],
                    'offer_code' => $offerData['offer_code'],
                ]);

                if ($offerData['offer'] && class_exists(OfferEligibilityService::class)) {
                    app(OfferEligibilityService::class)->recordUsage($offerData['offer'], [
                        'userId' => $order->patient_id,
                        'serviceId' => 0,
                    ], [
                        'booking_type' => 'test_order',
                        'booking_id' => $order->id,
                        'original_amount' => $totalAmount,
                        'discount_amount' => $discountAmount,
                        'final_amount' => $totalAmount - $discountAmount,
                    ]);
                }

                // FN-10: fire notification on order update
                if (function_exists('sendNotificationOnBookingUpdate')) {
                    sendNotificationOnBookingUpdate('test_order_updated', [
                        'id' => $order->id,
                        'type' => 'test_order_updated',
                        'patient_id' => $order->patient_id,
                        'order_number' => $order->order_number,
                    ]);
                }

                return $order;
            } else {
                $order = TestOrder::create([
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'clinical_notes' => $request->clinical_notes,
                    'priority' => $request->priority ?? 'routine',
                    'order_date' => now(),
                    'status' => TestOrder::STATUS_PENDING,
                    'admin_notes' => $request->admin_notes,
                ]);

                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $labTest = LabTest::findOrFail($item['lab_test_id']);
                    $price = $labTest->default_price;

                    TestOrderItem::create([
                        'test_order_id' => $order->id,
                        'lab_test_id' => $item['lab_test_id'],
                        'price' => $price,
                        'status' => TestOrderItem::STATUS_PENDING,
                    ]);

                    $totalAmount += $price;
                }

                $offerData = $this->applyOfferIfProvided($request, $totalAmount, $order->patient_id);
                $discountAmount = $offerData['discount_amount'];
                $order->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $totalAmount - $discountAmount,
                    'offer_id' => $offerData['offer_id'],
                    'offer_discount' => $offerData['discount_amount'],
                    'offer_code' => $offerData['offer_code'],
                ]);

                if ($offerData['offer'] && class_exists(OfferEligibilityService::class)) {
                    app(OfferEligibilityService::class)->recordUsage($offerData['offer'], [
                        'userId' => $order->patient_id,
                        'serviceId' => 0,
                    ], [
                        'booking_type' => 'test_order',
                        'booking_id' => $order->id,
                        'original_amount' => $totalAmount,
                        'discount_amount' => $discountAmount,
                        'final_amount' => $totalAmount - $discountAmount,
                    ]);
                }

                // Notification
                $notification_data = [
                    'id' => $order->id,
                    'type' => 'new_test_order',
                    'patient_id' => $order->patient_id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date,
                ];
                if (function_exists('sendNotificationOnBookingUpdate')) {
                    sendNotificationOnBookingUpdate('new_test_order', $notification_data);
                }

                return $order;
            }
        });

        $message = $orderId ? __('radiology_lab.order_update_success') : __('radiology_lab.order_save_success');

        return redirect()->route('backend.test-orders.show', $order->id)->withSuccess($message);
    }

    public function show($id)
    {
        $query = TestOrder::with(['patient', 'doctor', 'labTechnician', 'items.labTest', 'items.histories.changedBy', 'reports']);

        // Scope by role
        $user = auth()->user();
        if ($user->hasRole('lab_technician')) {
            $query->where('lab_technician_id', $user->id);
        } elseif ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }

        $order = $query->findOrFail($id);
        $availableTechnicians = User::role('lab_technician')->where('status', 1)->get();
        $allowedTransitions = TestOrder::ALLOWED_TRANSITIONS[$order->status] ?? [];
        $pageTitle = __('radiology_lab.order_detail');

        return view('radiologylab::backend.orders.show', compact('order', 'availableTechnicians', 'allowedTransitions', 'pageTitle'));
    }

    public function destroy($id)
    {
        $order = TestOrder::find($id);
        if ($order) {
            $order->delete();
            $msg = __('radiology_lab.order_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('radiology_lab.order_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $order ? true : false]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $order = TestOrder::withTrashed()->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => __('messages.not_found'), 'status' => false], 404);
        }

        $msg = __('messages.not_found_entry', ['name' => __('radiology_lab.order_singular')]);

        if ($request->type === 'restore') {
            $order->restore();
            $msg = __('messages.restore_form');
        } elseif ($request->type === 'forcedelete') {
            $order->forceDelete();
            $msg = __('messages.permanent_delete');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function assign_technician(Request $request, $id)
    {
        $request->validate([
            'lab_technician_id' => 'required|exists:users,id',
        ]);

        // Verify the user actually has the lab_technician role
        $technician = User::findOrFail($request->lab_technician_id);
        if (!$technician->hasRole('lab_technician')) {
            return response()->json(['status' => false, 'errors' => ['lab_technician_id' => [__('radiology_lab.user_not_lab_technician')]]], 422);
        }

        $query = TestOrder::query();
        $user = auth()->user();
        if ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }
        $order = $query->findOrFail($id);

        $order->update(['lab_technician_id' => $request->lab_technician_id]);

        // Notification
        $notification_data = [
            'id' => $order->id,
            'type' => 'test_order_technician_assigned',
            'patient_id' => $order->patient_id,
            'lab_technician_id' => $request->lab_technician_id,
            'order_number' => $order->order_number,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('test_order_technician_assigned', $notification_data);
        }

        $message = __('radiology_lab.technician_assign_success');

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function update_status(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', TestOrder::STATUSES),
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $query = TestOrder::query();
        $user = auth()->user();
        if ($user->hasRole('lab_technician')) {
            $query->where('lab_technician_id', $user->id);
        } elseif ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }

        $order = $query->findOrFail($id);
        $newStatus = $request->status;

        if (!$order->canTransitionTo($newStatus)) {
            return response()->json([
                'status' => false,
                'message' => __('radiology_lab.invalid_transition'),
            ], 422);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === TestOrder::STATUS_CANCELLED) {
            $updateData['cancelled_by'] = auth()->id();
            $updateData['cancellation_reason'] = $request->cancellation_reason ?? null;
        }

        $order->update($updateData);

        // Notification
        $notificationType = match ($newStatus) {
            TestOrder::STATUS_CONFIRMED => 'test_order_confirmed',
            TestOrder::STATUS_SAMPLE_COLLECTED => 'test_order_sample_collected',
            TestOrder::STATUS_PROCESSING => 'test_order_processing',
            TestOrder::STATUS_COMPLETED => 'test_order_completed',
            TestOrder::STATUS_DELIVERED => 'test_order_delivered',
            TestOrder::STATUS_CANCELLED => 'test_order_cancelled_by_admin',
            default => null,
        };

        if ($notificationType) {
            $notification_data = [
                'id' => $order->id,
                'type' => $notificationType,
                'patient_id' => $order->patient_id,
                'lab_technician_id' => $order->lab_technician_id,
                'order_number' => $order->order_number,
                'status' => $newStatus,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate($notificationType, $notification_data);
            }
        }

        return response()->json(['status' => true, 'message' => __('radiology_lab.status_update_success')]);
    }

    public function enter_results(TestResultFormRequest $request, $id)
    {
        $query = TestOrder::query();
        $user = auth()->user();
        if ($user->hasRole('lab_technician')) {
            $query->where('lab_technician_id', $user->id);
        }
        $order = $query->findOrFail($id);

        foreach ($request->results as $result) {
            $item = TestOrderItem::where('test_order_id', $order->id)->findOrFail($result['item_id']);

            if ($item->result_value !== null) {
                TestResultHistory::create([
                    'test_order_item_id' => $item->id,
                    'test_order_id' => $order->id,
                    'old_result_value' => $item->result_value,
                    'old_result_unit' => $item->result_unit,
                    'old_reference_range' => $item->reference_range,
                    'old_result_status' => $item->result_status,
                    'old_result_notes' => $item->result_notes,
                    'old_result_date' => $item->result_date,
                    'old_result_by' => $item->result_by,
                    'new_result_value' => $result['result_value'],
                    'new_result_unit' => $result['result_unit'] ?? null,
                    'new_reference_range' => $result['reference_range'] ?? null,
                    'new_result_status' => $result['result_status'] ?? null,
                    'new_result_notes' => $result['result_notes'] ?? null,
                    'changed_by' => auth()->id(),
                    'change_reason' => $result['change_reason'] ?? null,
                    'created_at' => now(),
                ]);
            }

            $item->update([
                'result_value' => $result['result_value'],
                'result_unit' => $result['result_unit'] ?? null,
                'reference_range' => $result['reference_range'] ?? null,
                'result_status' => $result['result_status'] ?? null,
                'result_notes' => $result['result_notes'] ?? null,
                'result_date' => now(),
                'result_by' => auth()->id(),
                'status' => TestOrderItem::STATUS_COMPLETED,
            ]);
        }

        // Check if all items have results
        $pendingItems = $order->items()->whereNull('result_value')->count();
        if ($pendingItems === 0 && $order->status === TestOrder::STATUS_PROCESSING) {
            $order->update(['status' => TestOrder::STATUS_COMPLETED]);
        }

        return response()->json(['status' => true, 'message' => __('radiology_lab.result_save_success')]);
    }

    public function upload_report(Request $request, $id)
    {
        $query = TestOrder::query();
        $user = auth()->user();
        if ($user->hasRole('lab_technician')) {
            $query->where('lab_technician_id', $user->id);
        }
        $order = $query->findOrFail($id);

        $request->validate([
            'report_file' => 'required|file|mimes:pdf|max:10240',
            'title' => 'required|string|max:255',
            'report_type' => 'nullable|in:lab_report,radiology_report,custom',
        ]);

        $report = TestReport::create([
            'test_order_id' => $order->id,
            'report_type' => $request->report_type ?? 'lab_report',
            'title' => $request->title,
            'notes' => $request->notes,
            'generated_by' => auth()->id(),
            'report_date' => now(),
            'status' => TestReport::STATUS_FINAL,
        ]);

        if ($request->hasFile('report_file')) {
            storeMediaFile($report, $request->file('report_file'), 'report_file');
        }

        // Notification
        $notification_data = [
            'id' => $order->id,
            'type' => 'test_report_ready',
            'patient_id' => $order->patient_id,
            'order_number' => $order->order_number,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('test_report_ready', $notification_data);
        }

        return redirect()->route('backend.test-orders.show', $order->id)->withSuccess(__('radiology_lab.report_upload_success'));
    }

    public function generate_report($id)
    {
        $query = TestOrder::with(['patient', 'doctor', 'labTechnician', 'items.labTest.category']);
        $user = auth()->user();
        if ($user->hasRole('lab_technician')) {
            $query->where('lab_technician_id', $user->id);
        }
        $order = $query->findOrFail($id);

        $template = $this->resolveReportTemplate($order);
        $reportType = $this->resolveReportType($order);

        // FN-11: build PDF only once; create TestReport row (and attach media) when none exists
        $existingReport = TestReport::where('test_order_id', $id)->latest()->first();
        $reportDate = $existingReport
            ? $existingReport->report_date->format('Y-m-d H:i:s')
            : now()->format('Y-m-d H:i:s');

        $pdf = Pdf::loadView($template, [
            'order' => $order,
            'items' => $order->items,
            'patient' => $order->patient,
            'doctor' => $order->doctor,
            'technician' => $order->labTechnician,
            'reportDate' => $reportDate,
        ])->setOptions(['defaultFont' => 'sans-serif']);

        if (!$existingReport) {
            $report = TestReport::create([
                'test_order_id' => $order->id,
                'report_type' => $reportType,
                'title' => ucfirst(str_replace('_', ' ', $reportType)) . ' - ' . $order->order_number,
                'generated_by' => auth()->id(),
                'report_date' => now(),
                'status' => TestReport::STATUS_FINAL,
            ]);

            // Persist PDF to MediaLibrary so future downloads reuse it (fixes FN-06 also)
            try {
                $tmpPath = tempnam(sys_get_temp_dir(), 'lab-rpt-') . '.pdf';
                file_put_contents($tmpPath, $pdf->output());
                $report->addMedia($tmpPath)
                    ->usingFileName('LAB-REPORT-' . $order->order_number . '.pdf')
                    ->toMediaCollection('report_file');
            } catch (\Throwable $e) {
                \Log::warning('Failed to persist generated report PDF: ' . $e->getMessage());
            }
        }

        return $pdf->stream('LAB-REPORT-' . $order->order_number . '.pdf');
    }

    public function download_report($reportId)
    {
        $report = TestReport::findOrFail($reportId);

        // Authorization scoping: verify the user has access to this report's order
        $user = auth()->user();
        $order = TestOrder::findOrFail($report->test_order_id);
        if ($user->hasRole('lab_technician') && $order->lab_technician_id !== $user->id) {
            abort(403, __('messages.permission_denied'));
        } elseif ($user->hasRole('doctor') && $order->doctor_id !== $user->id) {
            abort(403, __('messages.permission_denied'));
        }

        $fileUrl = $report->getFirstMediaUrl('report_file');

        if ($fileUrl) {
            $media = $report->getFirstMedia('report_file');
            return response()->download($media->getPath(), $report->title . '.pdf');
        }

        // Re-generate PDF if no file stored
        $order = TestOrder::with(['patient', 'doctor', 'labTechnician', 'items.labTest.category'])->findOrFail($report->test_order_id);

        $template = $this->resolveReportTemplate($order);

        $pdf = Pdf::loadView($template, [
            'order' => $order,
            'items' => $order->items,
            'patient' => $order->patient,
            'doctor' => $order->doctor,
            'technician' => $order->labTechnician,
            'reportDate' => now()->format('Y-m-d H:i:s'),
        ])->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->stream('LAB-REPORT-' . $order->order_number . '.pdf');
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'rowIds' => 'required|string',
            'action_type' => 'required|string|in:change-status,delete,restore,permanently-delete',
            'status' => 'required_if:action_type,change-status|in:' . implode(',', TestOrder::STATUSES),
        ]);

        $ids = array_filter(array_map('intval', explode(',', $request->rowIds)));
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        if ($actionType === 'change-status' && !auth()->user()->can('edit_test_orders')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }
        if (in_array($actionType, ['delete', 'permanently-delete', 'restore']) && !auth()->user()->can('delete_test_orders')) {
            return response()->json(['status' => false, 'message' => __('messages.permission_denied')], 403);
        }

        switch ($actionType) {
            case 'change-status':
                foreach ($ids as $id) {
                    $order = TestOrder::withTrashed()->find($id);
                    if ($order && $order->canTransitionTo($request->status)) {
                        $order->update(['status' => $request->status]);
                    }
                }
                $message = __('messages.bulk_service_update');
                break;

            case 'delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                TestOrder::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;

            case 'restore':
                TestOrder::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;

            case 'permanently-delete':
                if (config('app.demo_mode')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                TestOrder::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function update_admin_notes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $order = TestOrder::findOrFail($id);
        $order->update(['admin_notes' => $request->admin_notes]);

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.admin_notes_saved'),
        ]);
    }

    public function validate_coupon(Request $request)
    {
        $request->validate([
            'offer_code' => 'required|string|max:50',
            'patient_id' => 'nullable|integer',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if (! class_exists(OfferEligibilityService::class)) {
            return response()->json(['valid' => false, 'message' => __('messages.something_wrong')]);
        }

        $eligibilityService = app(OfferEligibilityService::class);
        $context = [
            'userId' => $request->patient_id ?? auth()->id(),
            'serviceId' => 0,
            'branchId' => null,
            'clinicId' => null,
            'serviceCharge' => (float) $request->total_amount,
            'channel' => 'admin',
            'paymentMethod' => null,
            'couponCode' => $request->offer_code,
        ];

        $result = $eligibilityService->validateCouponWithReason($request->offer_code, $context);

        if (! $result['valid']) {
            $reason = $result['reason'] ?? 'invalid_code';
            return response()->json([
                'valid' => false,
                'message' => __('radiology_lab.coupon_' . $reason, [], 'en') !== 'radiology_lab.coupon_' . $reason
                    ? __('radiology_lab.coupon_' . $reason)
                    : __('radiology_lab.coupon_invalid'),
            ]);
        }

        $offer = $result['offer'];
        $pricingService = app(OfferPricingService::class);
        $discount = $pricingService->calculateDiscount((float) $request->total_amount, collect([$offer]), $context);

        return response()->json([
            'valid' => true,
            'discount_amount' => $discount['discount_amount'],
            'message' => __('radiology_lab.coupon_applied', ['amount' => number_format($discount['discount_amount'], 2)]),
        ]);
    }

    private function applyOfferIfProvided(Request $request, float $totalAmount, int $patientId): array
    {
        // FN-02: never trust client-supplied discount_amount — only coupons produce discounts
        $default = ['discount_amount' => 0, 'offer_id' => null, 'offer_code' => null, 'offer' => null];

        $offerCode = $request->offer_code;
        if (! $offerCode || ! class_exists(OfferEligibilityService::class)) {
            return $default;
        }

        $eligibilityService = app(OfferEligibilityService::class);
        $context = [
            'userId' => $patientId,
            'serviceId' => 0,
            'branchId' => null,
            'clinicId' => null,
            'serviceCharge' => $totalAmount,
            'channel' => 'admin',
            'paymentMethod' => null,
            'couponCode' => $offerCode,
        ];

        $result = $eligibilityService->validateCouponWithReason($offerCode, $context);

        if (! $result['valid']) {
            return $default;
        }

        $offer = $result['offer'];
        $pricingService = app(OfferPricingService::class);
        $discount = $pricingService->calculateDiscount($totalAmount, collect([$offer]), $context);

        return [
            'discount_amount' => $discount['discount_amount'],
            'offer_id' => $offer->id,
            'offer_code' => $offer->code,
            'offer' => $offer,
        ];
    }

    private function resolveReportTemplate(TestOrder $order): string
    {
        $departments = $order->items
            ->map(fn($item) => $item->labTest->department ?? null)
            ->filter()
            ->unique();

        if ($departments->count() === 1) {
            return $departments->first() === 'radiology'
                ? 'radiologylab::backend.reports.radiology_report'
                : 'radiologylab::backend.reports.lab_report';
        }

        return 'radiologylab::backend.reports.pdf_report';
    }

    private function resolveReportType(TestOrder $order): string
    {
        $departments = $order->items
            ->map(fn($item) => $item->labTest->department ?? null)
            ->filter()
            ->unique();

        if ($departments->count() === 1) {
            return $departments->first() === 'radiology' ? 'radiology_report' : 'lab_report';
        }

        return 'lab_report';
    }
}
