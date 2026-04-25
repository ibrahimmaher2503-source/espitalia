<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Modules\RadiologyLab\Models\TestOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestReport;
use Modules\Offer\Services\OfferEligibilityService;
use Modules\Offer\Services\OfferPricingService;

class LabTestFrontController extends Controller
{
    public function testCatalog()
    {
        $pageTitle = __('radiology_lab.test_catalog');
        $categories = LabTestCategory::active()->ordered()->get();
        return view('frontend::lab_test_catalog', compact('pageTitle', 'categories'));
    }

    public function test_index_data(Request $request)
    {
        $query = LabTest::with('category')->active();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('sample_type')) {
            $query->where('sample_type', $request->sample_type);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('code', 'LIKE', "%$search%")
                  ->orWhere('description', 'LIKE', "%$search%");
            });
        }

        $perPage = $request->per_page ?? 12;
        $tests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $tests->map(function ($test) {
                return [
                    'id' => $test->id,
                    'name' => $test->name,
                    'code' => $test->code,
                    'category_name' => $test->category ? $test->category->name : '-',
                    'department' => $test->department,
                    'department_label' => $test->department === 'laboratory' ? __('radiology_lab.dept_laboratory') : __('radiology_lab.dept_radiology'),
                    'sample_type' => $test->sample_type,
                    'sample_type_label' => $test->sample_type_label,
                    'description' => $test->description,
                    'preparation_instructions' => $test->preparation_instructions,
                    'default_price' => number_format($test->default_price, 2),
                    'turnaround_time' => $test->turnaround_time ?? '-',
                    'profile_image' => asset('img/default-avatar.png'),
                ];
            }),
            'total' => $tests->total(),
            'last_page' => $tests->lastPage(),
        ]);
    }

    public function testDetail($id)
    {
        $test = LabTest::with('category')->active()->findOrFail($id);
        $pageTitle = $test->name;
        return view('frontend::lab_test_detail', compact('test', 'pageTitle'));
    }

    public function orderForm(Request $request)
    {
        $pageTitle = __('radiology_lab.order_a_test');
        $tests = LabTest::active()->orderBy('name')->get();
        $selectedTestId = $request->test_id ?? null;
        return view('frontend::order_test_form', compact('pageTitle', 'tests', 'selectedTestId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.lab_test_id' => 'required|exists:lab_tests,id',
            'clinical_notes' => 'nullable|string|max:2000',
            'offer_code' => 'nullable|string|max:50',
        ]);

        // Issue #7: Verify all requested tests are active
        $testIds = collect($request->items)->pluck('lab_test_id')->unique();
        $inactiveTests = LabTest::whereIn('id', $testIds)->where('status', '!=', 1)->exists();
        if ($inactiveTests) {
            return redirect()->back()->withErrors(__('radiology_lab.inactive_test_selected'))->withInput();
        }

        $order = DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $labTest = LabTest::findOrFail($item['lab_test_id']);
                $price = $labTest->default_price;
                $totalAmount += $price;
                $itemsData[] = [
                    'lab_test_id' => $labTest->id,
                    'price' => $price,
                    'status' => TestOrderItem::STATUS_PENDING,
                ];
            }

            $discountAmount = 0;
            $offerId = null;
            $offerCode = null;
            $appliedOffer = null;

            if ($request->offer_code && class_exists(OfferEligibilityService::class)) {
                $eligibilityService = app(OfferEligibilityService::class);
                $context = [
                    'userId' => auth()->id(),
                    'serviceId' => 0,
                    'branchId' => null,
                    'clinicId' => null,
                    'serviceCharge' => $totalAmount,
                    'channel' => 'web',
                    'paymentMethod' => null,
                    'couponCode' => $request->offer_code,
                ];

                $result = $eligibilityService->validateCouponWithReason($request->offer_code, $context);
                if ($result['valid']) {
                    $appliedOffer = $result['offer'];
                    $pricingService = app(OfferPricingService::class);
                    $discount = $pricingService->calculateDiscount($totalAmount, collect([$appliedOffer]), $context);
                    $discountAmount = $discount['discount_amount'];
                    $offerId = $appliedOffer->id;
                    $offerCode = $appliedOffer->code;
                } else {
                    // FN-13: surface coupon validation errors instead of silently dropping them
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        redirect()->back()->withErrors([
                            'offer_code' => __('radiology_lab.coupon_invalid'),
                        ])->withInput()
                    );
                }
            }

            $order = TestOrder::create([
                'patient_id' => auth()->id(),
                'order_date' => now(),
                'status' => TestOrder::STATUS_PENDING,
                'payment_status' => 'unpaid',
                'priority' => TestOrder::PRIORITY_ROUTINE,
                'clinical_notes' => $request->clinical_notes,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $totalAmount - $discountAmount,
                'offer_id' => $offerId,
                'offer_discount' => $discountAmount,
                'offer_code' => $offerCode,
            ]);

            foreach ($itemsData as $itemData) {
                $itemData['test_order_id'] = $order->id;
                TestOrderItem::create($itemData);
            }

            if ($appliedOffer) {
                $eligibilityService->recordUsage($appliedOffer, [
                    'userId' => auth()->id(),
                    'serviceId' => 0,
                ], [
                    'booking_type' => 'test_order',
                    'booking_id' => $order->id,
                    'original_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $totalAmount - $discountAmount,
                ]);
            }

            return $order;
        });

        // Send notification to admin about new test order
        $notification_data = [
            'id' => $order->id,
            'type' => 'new_test_order',
            'patient_id' => $order->patient_id,
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('new_test_order', $notification_data);
        }

        // FN-14: fallback if payment route is unavailable
        try {
            return redirect()->route('test-order-payment.show', $order->id)->withSuccess(__('radiology_lab.order_save_success'));
        } catch (\Throwable $e) {
            return redirect()->route('my-test-orders')->withSuccess(__('radiology_lab.order_save_success'));
        }
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'offer_code' => 'required|string|max:50',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if (! class_exists(OfferEligibilityService::class)) {
            return response()->json(['valid' => false, 'message' => __('messages.something_wrong')]);
        }

        $eligibilityService = app(OfferEligibilityService::class);
        $context = [
            'userId' => auth()->id(),
            'serviceId' => 0,
            'branchId' => null,
            'clinicId' => null,
            'serviceCharge' => (float) $request->total_amount,
            'channel' => 'web',
            'paymentMethod' => null,
            'couponCode' => $request->offer_code,
        ];

        $result = $eligibilityService->validateCouponWithReason($request->offer_code, $context);

        if (! $result['valid']) {
            return response()->json([
                'valid' => false,
                'message' => __('radiology_lab.coupon_invalid'),
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

    public function myOrders()
    {
        $pageTitle = __('radiology_lab.my_test_orders');
        return view('frontend::my_test_orders', compact('pageTitle'));
    }

    public function myOrders_index_data(Request $request)
    {
        $query = TestOrder::with(['items.labTest'])
            ->where('patient_id', auth()->id());

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $perPage = $request->per_page ?? 10;
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->order_date ? $order->order_date->format('d M Y') : '-',
                    'items_count' => $order->items->count(),
                    'test_names' => $order->items->map(function ($item) {
                        return $item->labTest ? $item->labTest->name : '-';
                    })->implode(', '),
                    'status' => $order->status,
                    'status_label' => __('radiology_lab.status_' . $order->status),
                    'priority' => $order->priority,
                    'priority_label' => __('radiology_lab.priority_' . $order->priority),
                    'total_amount' => number_format($order->total_amount, 2),
                    'payment_status' => $order->payment_status,
                    'payment_label' => __('radiology_lab.payment_' . $order->payment_status),
                    'created_at' => $order->created_at->format('d M Y'),
                ];
            }),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function orderDetail($id)
    {
        $order = TestOrder::with(['items.labTest', 'reports'])
            ->where('patient_id', auth()->id())
            ->findOrFail($id);

        $pageTitle = __('radiology_lab.order_detail') . ' #' . $order->order_number;
        return view('frontend::test_order_detail', compact('order', 'pageTitle'));
    }

    public function downloadReport($id)
    {
        $order = TestOrder::with(['reports', 'items.labTest.category', 'patient'])
            ->where('patient_id', auth()->id())
            ->findOrFail($id);

        // Try to find a final report with attached file
        $report = $order->reports()
            ->where('status', TestReport::STATUS_FINAL)
            ->latest()
            ->first();

        if ($report) {
            $media = $report->getFirstMedia('report_file');
            if ($media) {
                return response()->download($media->getPath(), $report->title . '.pdf');
            }
        }

        // Auto-generate a PDF report if no file exists but results are available
        $completedItems = $order->items->filter(function ($item) {
            return ! empty($item->result_value);
        });

        if ($completedItems->isEmpty()) {
            return redirect()->back()->withErrors(__('radiology_lab.report_download_not_available'));
        }

        $order->load(['doctor', 'labTechnician']);

        $departments = $order->items
            ->map(fn($item) => $item->labTest->department ?? null)
            ->filter()
            ->unique();

        $template = 'radiologylab::backend.reports.pdf_report';
        if ($departments->count() === 1) {
            $template = $departments->first() === 'radiology'
                ? 'radiologylab::backend.reports.radiology_report'
                : 'radiologylab::backend.reports.lab_report';
        }

        $pdf = Pdf::loadView($template, [
            'order' => $order,
            'items' => $order->items,
            'patient' => $order->patient,
            'doctor' => $order->doctor,
            'technician' => $order->labTechnician,
            'reportDate' => now()->format('d M Y'),
        ])->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->download('LAB-REPORT-' . $order->order_number . '.pdf');
    }

    public function cancel(Request $request, $id)
    {
        $order = TestOrder::where('patient_id', auth()->id())->findOrFail($id);

        if (! $order->canTransitionTo(TestOrder::STATUS_CANCELLED)) {
            return redirect()->back()->withErrors(__('radiology_lab.cannot_cancel_completed'));
        }

        $order->update([
            'status' => TestOrder::STATUS_CANCELLED,
            'cancelled_by' => auth()->id(),
            'cancellation_reason' => $request->cancellation_reason ?? null,
        ]);

        // Send notification about order cancellation
        $notification_data = [
            'id' => $order->id,
            'type' => 'test_order_cancelled',
            'patient_id' => $order->patient_id,
            'order_number' => $order->order_number,
            'cancellation_reason' => $request->cancellation_reason ?? '',
        ];
        if (function_exists('sendNotificationOnBookingUpdate')) {
            sendNotificationOnBookingUpdate('test_order_cancelled', $notification_data);
        }

        return redirect()->route('my-test-orders')->withSuccess(__('radiology_lab.order_cancel_success'));
    }
}
