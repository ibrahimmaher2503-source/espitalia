<?php

namespace Modules\RadiologyLab\database\seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestReport;

class TestOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            $patients = User::role('user')->take(5)->get();
            if ($patients->isEmpty()) {
                $patients = User::take(5)->get();
            }

            $doctors = User::role('doctor')->take(3)->get();
            $technicians = User::role('lab_technician')->get();
            $labTests = LabTest::where('status', 1)->get();

            if ($patients->isEmpty() || $labTests->isEmpty()) {
                return;
            }

            $admin = User::role('admin')->first();
            $adminId = $admin->id ?? 1;

            $orders = [
                // Completed order with results and final report
                [
                    'patient' => $patients[0] ?? $patients->first(),
                    'doctor' => $doctors[0] ?? null,
                    'technician' => $technicians[0] ?? null,
                    'status' => TestOrder::STATUS_DELIVERED,
                    'payment_status' => 'paid',
                    'priority' => TestOrder::PRIORITY_ROUTINE,
                    'clinical_notes' => 'Annual checkup - routine blood work',
                    'order_date' => Carbon::now()->subDays(10),
                    'tests' => $labTests->where('department', 'laboratory')->take(3),
                    'with_results' => true,
                    'with_report' => true,
                ],
                // Processing order with sample collected
                [
                    'patient' => $patients[1] ?? $patients->first(),
                    'doctor' => $doctors[1] ?? $doctors->first() ?? null,
                    'technician' => $technicians[0] ?? null,
                    'status' => TestOrder::STATUS_PROCESSING,
                    'payment_status' => 'paid',
                    'priority' => TestOrder::PRIORITY_URGENT,
                    'clinical_notes' => 'Patient complaining of fatigue and dizziness',
                    'order_date' => Carbon::now()->subDays(2),
                    'tests' => $labTests->whereIn('code', ['CBC-001', 'LFT-001', 'KFT-001', 'THY-001']),
                    'with_results' => false,
                    'with_report' => false,
                ],
                // Pending order
                [
                    'patient' => $patients[2] ?? $patients->first(),
                    'doctor' => $doctors[0] ?? null,
                    'technician' => null,
                    'status' => TestOrder::STATUS_PENDING,
                    'payment_status' => 'unpaid',
                    'priority' => TestOrder::PRIORITY_ROUTINE,
                    'clinical_notes' => 'Pre-surgery lab work required',
                    'order_date' => Carbon::now(),
                    'tests' => $labTests->whereIn('code', ['CBC-001', 'LFT-001', 'KFT-001']),
                    'with_results' => false,
                    'with_report' => false,
                ],
                // Confirmed order - urgent
                [
                    'patient' => $patients[3] ?? $patients->first(),
                    'doctor' => $doctors[2] ?? $doctors->first() ?? null,
                    'technician' => $technicians[0] ?? null,
                    'status' => TestOrder::STATUS_CONFIRMED,
                    'payment_status' => 'paid',
                    'priority' => TestOrder::PRIORITY_STAT,
                    'clinical_notes' => 'Emergency - suspected internal bleeding',
                    'order_date' => Carbon::now(),
                    'tests' => $labTests->whereIn('code', ['CBC-001', 'LFT-001']),
                    'with_results' => false,
                    'with_report' => false,
                ],
                // Completed radiology order with report
                [
                    'patient' => $patients[0] ?? $patients->first(),
                    'doctor' => $doctors[1] ?? $doctors->first() ?? null,
                    'technician' => $technicians[0] ?? null,
                    'status' => TestOrder::STATUS_COMPLETED,
                    'payment_status' => 'paid',
                    'priority' => TestOrder::PRIORITY_ROUTINE,
                    'clinical_notes' => 'Follow-up imaging for chest pain',
                    'order_date' => Carbon::now()->subDays(5),
                    'tests' => $labTests->where('department', 'radiology')->take(2),
                    'with_results' => true,
                    'with_report' => true,
                ],
                // Cancelled order
                [
                    'patient' => $patients[4] ?? $patients->first(),
                    'doctor' => null,
                    'technician' => null,
                    'status' => TestOrder::STATUS_CANCELLED,
                    'payment_status' => 'refunded',
                    'priority' => TestOrder::PRIORITY_ROUTINE,
                    'clinical_notes' => 'Routine screening',
                    'order_date' => Carbon::now()->subDays(7),
                    'tests' => $labTests->whereIn('code', ['UA-001', 'VTD-001']),
                    'with_results' => false,
                    'with_report' => false,
                    'cancellation_reason' => 'Patient requested cancellation',
                ],
            ];

            $resultStatuses = ['normal', 'abnormal', 'critical'];
            $resultUnits = ['mg/dL', 'g/dL', 'U/L', 'mmol/L', '%', 'cells/mcL', 'ng/mL', 'mIU/L'];

            foreach ($orders as $orderData) {
                $tests = $orderData['tests'];
                if ($tests->isEmpty()) {
                    continue;
                }

                $totalAmount = $tests->sum('default_price');

                $order = TestOrder::create([
                    'patient_id' => $orderData['patient']->id,
                    'doctor_id' => $orderData['doctor']->id ?? null,
                    'lab_technician_id' => $orderData['technician']->id ?? null,
                    'clinical_notes' => $orderData['clinical_notes'],
                    'priority' => $orderData['priority'],
                    'order_date' => $orderData['order_date'],
                    'status' => $orderData['status'],
                    'payment_status' => $orderData['payment_status'],
                    'total_amount' => $totalAmount,
                    'discount_amount' => 0,
                    'final_amount' => $totalAmount,
                    'admin_notes' => null,
                    'cancelled_by' => $orderData['status'] === TestOrder::STATUS_CANCELLED ? $adminId : null,
                    'cancellation_reason' => $orderData['cancellation_reason'] ?? null,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]);

                foreach ($tests as $test) {
                    $itemStatus = match ($orderData['status']) {
                        TestOrder::STATUS_DELIVERED, TestOrder::STATUS_COMPLETED => TestOrderItem::STATUS_COMPLETED,
                        TestOrder::STATUS_PROCESSING => TestOrderItem::STATUS_PROCESSING,
                        TestOrder::STATUS_SAMPLE_COLLECTED => TestOrderItem::STATUS_SAMPLE_COLLECTED,
                        default => TestOrderItem::STATUS_PENDING,
                    };

                    $itemData = [
                        'test_order_id' => $order->id,
                        'lab_test_id' => $test->id,
                        'price' => $test->default_price,
                        'status' => $itemStatus,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ];

                    if ($orderData['with_results']) {
                        $itemData['result_value'] = $this->generateResultValue($test->code);
                        $itemData['result_unit'] = $this->getResultUnit($test->code);
                        $itemData['reference_range'] = $this->getReferenceRange($test->code);
                        $itemData['result_status'] = $resultStatuses[array_rand($resultStatuses)];
                        $itemData['result_notes'] = 'Within expected parameters';
                        $itemData['result_date'] = $orderData['order_date']->copy()->addDays(1);
                        $itemData['result_by'] = $orderData['technician']->id ?? $adminId;
                    }

                    TestOrderItem::create($itemData);
                }

                if ($orderData['with_report']) {
                    $isRadiology = $tests->first()->department === 'radiology';

                    TestReport::create([
                        'test_order_id' => $order->id,
                        'report_type' => $isRadiology ? TestReport::TYPE_RADIOLOGY_REPORT : TestReport::TYPE_LAB_REPORT,
                        'title' => $isRadiology
                            ? 'Radiology Report - ' . $order->order_number
                            : 'Lab Report - ' . $order->order_number,
                        'notes' => $isRadiology
                            ? 'Imaging results reviewed. No acute abnormalities identified. Follow-up recommended in 6 months.'
                            : 'All results are within normal reference ranges. No significant abnormalities detected.',
                        'generated_by' => $orderData['technician']->id ?? $adminId,
                        'report_date' => $orderData['order_date']->copy()->addDays(2),
                        'status' => TestReport::STATUS_FINAL,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ]);
                }
            }
        }
    }

    private function generateResultValue(string $code): string
    {
        return match ($code) {
            'CBC-001' => '14.2',
            'LFT-001' => '35',
            'KFT-001' => '0.9',
            'HBA-001' => '5.4',
            'LPD-001' => '190',
            'VTD-001' => '32',
            'UA-001' => 'Normal',
            'THY-001' => '2.1',
            'HCG-001' => 'Negative',
            default => '12.5',
        };
    }

    private function getResultUnit(string $code): string
    {
        return match ($code) {
            'CBC-001' => 'g/dL',
            'LFT-001' => 'U/L',
            'KFT-001' => 'mg/dL',
            'HBA-001' => '%',
            'LPD-001' => 'mg/dL',
            'VTD-001' => 'ng/mL',
            'UA-001' => '',
            'THY-001' => 'mIU/L',
            'HCG-001' => 'mIU/mL',
            default => 'mg/dL',
        };
    }

    private function getReferenceRange(string $code): string
    {
        return match ($code) {
            'CBC-001' => '12.0-17.5 g/dL',
            'LFT-001' => '7-56 U/L',
            'KFT-001' => '0.7-1.3 mg/dL',
            'HBA-001' => '4.0-5.6%',
            'LPD-001' => '<200 mg/dL',
            'VTD-001' => '20-50 ng/mL',
            'UA-001' => 'Normal',
            'THY-001' => '0.4-4.0 mIU/L',
            'HCG-001' => '<5 mIU/mL',
            default => 'N/A',
        };
    }
}
