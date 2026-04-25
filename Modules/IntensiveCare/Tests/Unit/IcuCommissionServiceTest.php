<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;
use Modules\IntensiveCare\Models\IcuDepartment;
use Modules\IntensiveCare\Services\IcuCommissionService;

class IcuCommissionServiceTest extends BaseTest
{
    protected IcuCommissionService $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = new IcuCommissionService;
    }

    // ── Percentage Calculation ──────────────────────────────────

    public function test_percentage_calculation_15_percent_of_1000()
    {
        $commission = IcuCommission::create([
            'title' => 'Standard 15%',
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => 15.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 1000.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNotNull($earning);
        $this->assertEquals('150.00', $earning->commission_amount);
        $this->assertEquals('850.00', $earning->hospital_receivable);
        $this->assertEquals('percentage', $earning->commission_type);
        $this->assertEquals('15.00', $earning->commission_value);
        $this->assertEquals('1000.00', $earning->order_subtotal);
    }

    // ── Fixed Calculation ───────────────────────────────────────

    public function test_fixed_calculation_50_on_500()
    {
        $commission = IcuCommission::create([
            'title' => 'Fixed $50',
            'commission_type' => IcuCommission::TYPE_FIXED,
            'commission_value' => 50.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 500.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNotNull($earning);
        $this->assertEquals('50.00', $earning->commission_amount);
        $this->assertEquals('450.00', $earning->hospital_receivable);
        $this->assertEquals('fixed', $earning->commission_type);
    }

    // ── Fixed Capped at Subtotal ────────────────────────────────

    public function test_fixed_capped_at_subtotal_prevents_negative_receivable()
    {
        $commission = IcuCommission::create([
            'title' => 'Fixed $200',
            'commission_type' => IcuCommission::TYPE_FIXED,
            'commission_value' => 200.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 100.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNotNull($earning);
        // Fixed commission capped at subtotal of 100
        $this->assertEquals('100.00', $earning->commission_amount);
        $this->assertEquals('0.00', $earning->hospital_receivable);
    }

    // ── Duplicate Prevention ────────────────────────────────────

    public function test_duplicate_prevention_returns_null_on_second_call()
    {
        $commission = IcuCommission::create([
            'title' => 'Test Commission',
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 1000.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning1 = $this->commissionService->calculateCommission($admissionRequest);
        $earning2 = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNotNull($earning1);
        $this->assertNull($earning2);

        // Verify only one earning exists
        $count = IcuCommissionEarning::where('admission_request_id', $admissionRequest->id)->count();
        $this->assertEquals(1, $count);
    }

    // ── No Commission Assigned ──────────────────────────────────

    public function test_no_commission_assigned_returns_null()
    {
        $hospital = Hospital::factory()->create(['commission_id' => null]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 1000.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNull($earning);
    }

    // ── Zero Subtotal ───────────────────────────────────────────

    public function test_zero_subtotal_produces_zero_amounts()
    {
        $commission = IcuCommission::create([
            'title' => 'Test Commission',
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 0.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        $this->assertNotNull($earning);
        $this->assertEquals('0.00', $earning->commission_amount);
        $this->assertEquals('0.00', $earning->hospital_receivable);
    }

    // ── Reverse Commission ──────────────────────────────────────

    public function test_reverse_commission_marks_as_cancelled()
    {
        $commission = IcuCommission::create([
            'title' => 'Test Commission',
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 1000.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);
        $this->assertEquals(IcuCommissionEarning::STATUS_UNPAID, $earning->status);

        $result = $this->commissionService->reverseCommission($admissionRequest);

        $this->assertTrue($result);
        $earning->refresh();
        $this->assertEquals(IcuCommissionEarning::STATUS_CANCELLED, $earning->status);
    }

    // ── Reverse Rejects Paid ────────────────────────────────────

    public function test_reverse_commission_rejects_paid_earnings()
    {
        $commission = IcuCommission::create([
            'title' => 'Test Commission',
            'commission_type' => IcuCommission::TYPE_PERCENTAGE,
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'daily_price' => 1000.00,
        ]);

        $admissionRequest = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ]);

        $earning = $this->commissionService->calculateCommission($admissionRequest);

        // Manually mark as paid
        $earning->update(['status' => IcuCommissionEarning::STATUS_PAID]);

        $result = $this->commissionService->reverseCommission($admissionRequest);

        $this->assertFalse($result);
        $earning->refresh();
        $this->assertEquals(IcuCommissionEarning::STATUS_PAID, $earning->status);
    }
}
