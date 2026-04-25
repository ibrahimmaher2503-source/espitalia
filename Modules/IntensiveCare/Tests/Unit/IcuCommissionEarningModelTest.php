<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;
use Modules\IntensiveCare\Models\IcuPayout;

class IcuCommissionEarningModelTest extends BaseTest
{
    // ── Fillable Tests ─────────────────────────────────────────

    public function test_fillable_contains_expected_fields()
    {
        $earning = new IcuCommissionEarning;
        $fillable = $earning->getFillable();

        $this->assertContains('admission_request_id', $fillable);
        $this->assertContains('hospital_id', $fillable);
        $this->assertContains('commission_id', $fillable);
        $this->assertContains('order_subtotal', $fillable);
        $this->assertContains('commission_type', $fillable);
        $this->assertContains('commission_value', $fillable);
        $this->assertContains('commission_amount', $fillable);
        $this->assertContains('hospital_receivable', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('payout_id', $fillable);
    }

    // ── Cast Tests ─────────────────────────────────────────────

    public function test_order_subtotal_is_cast_to_decimal()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $earning = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.50,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.05,
            'hospital_receivable' => 900.45,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $this->assertEquals('1000.50', $earning->order_subtotal);
        $this->assertEquals('10.00', $earning->commission_value);
        $this->assertEquals('100.05', $earning->commission_amount);
        $this->assertEquals('900.45', $earning->hospital_receivable);
    }

    // ── Constants Tests ────────────────────────────────────────

    public function test_status_unpaid_constant()
    {
        $this->assertEquals('unpaid', IcuCommissionEarning::STATUS_UNPAID);
    }

    public function test_status_paid_constant()
    {
        $this->assertEquals('paid', IcuCommissionEarning::STATUS_PAID);
    }

    public function test_status_cancelled_constant()
    {
        $this->assertEquals('cancelled', IcuCommissionEarning::STATUS_CANCELLED);
    }

    // ── Scope Tests ────────────────────────────────────────────

    public function test_unpaid_scope_returns_only_unpaid_earnings()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $unpaid = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $paid = IcuCommissionEarning::create([
            'admission_request_id' => 2,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 500.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 50.00,
            'hospital_receivable' => 450.00,
            'status' => IcuCommissionEarning::STATUS_PAID,
        ]);

        $results = IcuCommissionEarning::unpaid()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($unpaid));
        $this->assertFalse($results->contains($paid));
    }

    public function test_paid_scope_returns_only_paid_earnings()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $unpaid = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $paid = IcuCommissionEarning::create([
            'admission_request_id' => 2,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 500.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 50.00,
            'hospital_receivable' => 450.00,
            'status' => IcuCommissionEarning::STATUS_PAID,
        ]);

        $results = IcuCommissionEarning::paid()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($paid));
        $this->assertFalse($results->contains($unpaid));
    }

    public function test_for_hospital_scope_filters_by_hospital_id()
    {
        $hospital1 = Hospital::factory()->create();
        $hospital2 = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $earning1 = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital1->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $earning2 = IcuCommissionEarning::create([
            'admission_request_id' => 2,
            'hospital_id' => $hospital2->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 500.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 50.00,
            'hospital_receivable' => 450.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $results = IcuCommissionEarning::forHospital($hospital1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($earning1));
        $this->assertFalse($results->contains($earning2));
    }

    // ── Relationship Tests ─────────────────────────────────────

    public function test_admission_request_relationship()
    {
        $hospital = Hospital::factory()->create();
        $request = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital->id]);
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $earning = IcuCommissionEarning::create([
            'admission_request_id' => $request->id,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $this->assertInstanceOf(IcuAdmissionRequest::class, $earning->admissionRequest);
        $this->assertEquals($request->id, $earning->admissionRequest->id);
    }

    public function test_hospital_relationship()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $earning = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $this->assertInstanceOf(Hospital::class, $earning->hospital);
        $this->assertEquals($hospital->id, $earning->hospital->id);
    }

    public function test_commission_relationship()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test Commission', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $earning = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_UNPAID,
        ]);

        $this->assertInstanceOf(IcuCommission::class, $earning->commission);
        $this->assertEquals($commission->id, $earning->commission->id);
    }

    public function test_payout_relationship()
    {
        $hospital = Hospital::factory()->create();
        $commission = IcuCommission::create([
            'title' => 'Test', 'commission_type' => 'percentage',
            'commission_value' => 10.00, 'status' => true,
        ]);

        $payout = IcuPayout::create([
            'hospital_id' => $hospital->id,
            'total_amount' => 900.00,
            'total_commission' => 100.00,
            'payment_method' => IcuPayout::PAYMENT_METHOD_CASH,
            'payment_date' => now(),
        ]);

        $earning = IcuCommissionEarning::create([
            'admission_request_id' => 1,
            'hospital_id' => $hospital->id,
            'commission_id' => $commission->id,
            'order_subtotal' => 1000.00,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'commission_amount' => 100.00,
            'hospital_receivable' => 900.00,
            'status' => IcuCommissionEarning::STATUS_PAID,
            'payout_id' => $payout->id,
        ]);

        $this->assertInstanceOf(IcuPayout::class, $earning->payout);
        $this->assertEquals($payout->id, $earning->payout->id);
    }
}
