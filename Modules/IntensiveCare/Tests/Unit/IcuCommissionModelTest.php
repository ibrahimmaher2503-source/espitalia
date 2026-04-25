<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuCommissionEarning;

class IcuCommissionModelTest extends BaseTest
{
    // ── Fillable Tests ─────────────────────────────────────────

    public function test_fillable_contains_expected_fields()
    {
        $commission = new IcuCommission;
        $fillable = $commission->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('commission_type', $fillable);
        $this->assertContains('commission_value', $fillable);
        $this->assertContains('status', $fillable);
    }

    // ── Cast Tests ─────────────────────────────────────────────

    public function test_status_is_cast_to_boolean()
    {
        $commission = IcuCommission::create([
            'title' => 'Test Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'status' => 1,
        ]);

        $this->assertIsBool($commission->status);
        $this->assertTrue($commission->status);
    }

    public function test_status_false_is_cast_to_boolean()
    {
        $commission = IcuCommission::create([
            'title' => 'Inactive Commission',
            'commission_type' => 'fixed',
            'commission_value' => 25.00,
            'status' => 0,
        ]);

        $this->assertIsBool($commission->status);
        $this->assertFalse($commission->status);
    }

    public function test_commission_value_is_cast_to_decimal()
    {
        $commission = IcuCommission::create([
            'title' => 'Decimal Test',
            'commission_type' => 'percentage',
            'commission_value' => 15.50,
            'status' => true,
        ]);

        $this->assertEquals('15.50', $commission->commission_value);
    }

    // ── Constants Tests ────────────────────────────────────────

    public function test_type_percentage_constant()
    {
        $this->assertEquals('percentage', IcuCommission::TYPE_PERCENTAGE);
    }

    public function test_type_fixed_constant()
    {
        $this->assertEquals('fixed', IcuCommission::TYPE_FIXED);
    }

    // ── Scope Tests ────────────────────────────────────────────

    public function test_active_scope_returns_only_active_commissions()
    {
        $active = IcuCommission::create([
            'title' => 'Active Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $inactive = IcuCommission::create([
            'title' => 'Inactive Commission',
            'commission_type' => 'fixed',
            'commission_value' => 25.00,
            'status' => false,
        ]);

        $results = IcuCommission::active()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    // ── Relationship Tests ─────────────────────────────────────

    public function test_hospitals_relationship_returns_has_many()
    {
        $commission = IcuCommission::create([
            'title' => 'Hospital Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital1 = Hospital::factory()->create(['commission_id' => $commission->id]);
        $hospital2 = Hospital::factory()->create(['commission_id' => $commission->id]);

        $this->assertInstanceOf(Hospital::class, $commission->hospitals()->getRelated());
        $this->assertCount(2, $commission->hospitals);
        $this->assertTrue($commission->hospitals->contains($hospital1));
        $this->assertTrue($commission->hospitals->contains($hospital2));
    }

    public function test_earnings_relationship_returns_has_many()
    {
        $commission = IcuCommission::create([
            'title' => 'Earnings Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create(['commission_id' => $commission->id]);

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

        $this->assertInstanceOf(IcuCommissionEarning::class, $commission->earnings()->getRelated());
        $this->assertCount(1, $commission->earnings);
        $this->assertTrue($commission->earnings->contains($earning));
    }
}
