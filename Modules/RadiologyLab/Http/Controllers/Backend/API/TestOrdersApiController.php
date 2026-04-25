<?php

namespace Modules\RadiologyLab\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestReport;
use Modules\RadiologyLab\Transformers\TestOrderResource;
use PDF;

class TestOrdersApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = TestOrder::with(['patient', 'labTechnician', 'items.labTest', 'reports']);

            $user = auth()->user();

            if ($user->hasRole('lab_technician')) {
                $query->where('lab_technician_id', $user->id);
            } elseif ($user->hasRole('doctor')) {
                $query->where('doctor_id', $user->id);
            } elseif ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            }
            // admin/demo_admin see all

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $perPage = min((int) ($request->per_page ?? 15), 100);
            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return TestOrderResource::collection($orders)->additional(['status' => true]);
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.lab_test_id' => 'required|exists:lab_tests,id',
                'clinical_notes' => 'nullable|string|max:2000',
                'priority' => 'nullable|in:routine,urgent,stat',
                'doctor_id' => 'nullable|exists:users,id',
                'patient_id' => 'nullable|exists:users,id',
            ]);

            $user = auth()->user();

            $order = DB::transaction(function () use ($request, $user) {
                $data = [
                    'patient_id' => ($user->hasRole('user')) ? $user->id : ($request->patient_id ?? $user->id),
                    'doctor_id' => $request->doctor_id,
                    'clinical_notes' => $request->clinical_notes,
                    'priority' => $request->priority ?? 'routine',
                    'order_date' => now()->toDateString(),
                    'status' => TestOrder::STATUS_PENDING,
                    'payment_status' => 'unpaid',
                ];

                $order = TestOrder::create($data);

                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $labTest = LabTest::where('id', $item['lab_test_id'])->where('status', 1)->first();
                    if (!$labTest) {
                        throw new \Exception(__('radiology_lab.test_not_found'));
                    }
                    $price = $labTest->default_price;

                    TestOrderItem::create([
                        'test_order_id' => $order->id,
                        'lab_test_id' => $labTest->id,
                        'price' => $price,
                        'status' => TestOrderItem::STATUS_PENDING,
                    ]);

                    $totalAmount += $price;
                }

                // VL-12: use the same coupon_code-based flow as the admin + frontend paths
                $offerDiscount = 0;
                $offerData = [];
                $appliedOffer = null;
                $couponCode = $request->input('coupon_code') ?? $request->input('offer_code');

                if ($couponCode && class_exists(\Modules\Offer\Services\OfferEligibilityService::class)) {
                    try {
                        $eligibility = app(\Modules\Offer\Services\OfferEligibilityService::class);
                        $pricing = app(\Modules\Offer\Services\OfferPricingService::class);
                        $context = [
                            'userId' => $order->patient_id,
                            'serviceId' => 0,
                            'branchId' => null,
                            'clinicId' => null,
                            'serviceCharge' => $totalAmount,
                            'channel' => 'app',
                            'paymentMethod' => null,
                            'couponCode' => $couponCode,
                        ];
                        $result = $eligibility->validateCouponWithReason($couponCode, $context);
                        if ($result['valid']) {
                            $appliedOffer = $result['offer'];
                            $calc = $pricing->calculateDiscount($totalAmount, collect([$appliedOffer]), $context);
                            $offerDiscount = round($calc['discount_amount'], 2);
                            $offerData = [
                                'offer_id' => $appliedOffer->id,
                                'offer_discount' => $offerDiscount,
                                'offer_code' => $appliedOffer->code,
                            ];
                        }
                    } catch (\Throwable $e) {
                        \Log::warning('Offer discount on test order failed: ' . $e->getMessage());
                    }
                }

                $order->update(array_merge([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $offerDiscount,
                    'final_amount' => $totalAmount - $offerDiscount,
                ], $offerData));

                // Record offer usage with the correct signature
                if ($appliedOffer) {
                    try {
                        app(\Modules\Offer\Services\OfferEligibilityService::class)->recordUsage($appliedOffer, [
                            'userId' => $order->patient_id,
                            'serviceId' => 0,
                        ], [
                            'booking_type' => 'test_order',
                            'booking_id' => $order->id,
                            'original_amount' => $totalAmount,
                            'discount_amount' => $offerDiscount,
                            'final_amount' => $totalAmount - $offerDiscount,
                        ]);
                    } catch (\Throwable $e) {
                        \Log::warning('Offer usage recording failed: ' . $e->getMessage());
                    }
                }

                return $order;
            });

            $order->load(['patient', 'items.labTest', 'reports']);

            // Send notification
            $notification_data = [
                'id' => $order->id,
                'type' => 'new_test_order',
                'patient_id' => $order->patient_id,
                'order_number' => $order->order_number,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('new_test_order', $notification_data);
            }

            return response()->json([
                'status' => true,
                'message' => __('radiology_lab.order_save_success'),
                'data' => new TestOrderResource($order),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function show($id)
    {
        try {
            $query = TestOrder::with(['patient', 'doctor', 'labTechnician', 'items.labTest', 'reports']);

            $user = auth()->user();
            if ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            } elseif ($user->hasRole('lab_technician')) {
                $query->where('lab_technician_id', $user->id);
            } elseif ($user->hasRole('doctor')) {
                $query->where('doctor_id', $user->id);
            }

            $order = $query->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => new TestOrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('radiology_lab.order_not_found')], 404);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $request->validate([
                'cancellation_reason' => 'required|string|max:500',
            ]);

            $user = auth()->user();
            $query = TestOrder::query();

            if ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            } elseif ($user->hasRole('doctor')) {
                $query->where('doctor_id', $user->id);
            }
            // admin/demo_admin can cancel any order

            $order = $query->findOrFail($id);

            if (! $order->canTransitionTo(TestOrder::STATUS_CANCELLED)) {
                return response()->json([
                    'status' => false,
                    'message' => __('radiology_lab.cannot_cancel_completed'),
                ], 422);
            }

            $order->update([
                'status' => TestOrder::STATUS_CANCELLED,
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            // Send notification
            $notification_data = [
                'id' => $order->id,
                'type' => 'test_order_cancelled',
                'patient_id' => $order->patient_id,
                'order_number' => $order->order_number,
                'cancellation_reason' => $request->cancellation_reason,
            ];
            if (function_exists('sendNotificationOnBookingUpdate')) {
                sendNotificationOnBookingUpdate('test_order_cancelled', $notification_data);
            }

            return response()->json([
                'status' => true,
                'message' => __('radiology_lab.order_cancel_success'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function downloadReport($id)
    {
        try {
            $user = auth()->user();
            $query = TestOrder::query();

            if ($user->hasRole('user')) {
                $query->where('patient_id', $user->id);
            } elseif ($user->hasRole('lab_technician')) {
                $query->where('lab_technician_id', $user->id);
            } elseif ($user->hasRole('doctor')) {
                $query->where('doctor_id', $user->id);
            }
            // admin/demo_admin can download any report

            $order = $query->findOrFail($id);

            $report = $order->reports()->where('status', 'final')->latest()->first();

            if ($report && $report->getFirstMedia('report_file')) {
                return response()->download($report->getFirstMedia('report_file')->getPath());
            }

            // Auto-generate if no stored report
            $order->load(['patient', 'doctor', 'labTechnician', 'items.labTest']);

            $data = [
                'order' => $order,
                'items' => $order->items,
                'patient' => $order->patient,
                'doctor' => $order->doctor,
                'technician' => $order->labTechnician,
                'reportDate' => now()->format('d M Y'),
            ];

            $pdf = PDF::loadView('radiologylab::backend.reports.pdf_report', $data);

            return $pdf->download('LAB-REPORT-' . $order->order_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('RadiologyLab API error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
