<?php

namespace Modules\Pharmacy\Tests\Feature;

use App\Models\User;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCommission;
use Modules\Pharmacy\Models\PharmacyCommissionEarning;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Services\PharmacyCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyCommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $commissionService;

    protected $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = new PharmacyCommissionService;
        $this->patient = User::factory()->create();
    }

    /** @test */
    public function test_percentage_commission_calculation_produces_correct_amounts()
    {
        // Create commission template: 10% percentage
        $commission = PharmacyCommission::create([
            'title' => 'Standard 10%',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => true,
        ]);

        // Create pharmacy with commission
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'commission_id' => $commission->id,
            'is_active' => true,
        ]);

        // Create order with $100 subtotal
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        // Calculate commission
        $earning = $this->commissionService->calculateCommission($order);

        // Assert earning was created
        $this->assertNotNull($earning);
        $this->assertEquals($order->id, $earning->order_id);

        // Assert amounts are correct
        // Commission: 100 * 10% = 10
        // Pharmacy receivable: 100 - 10 = 90
        $this->assertEquals(10.00, $earning->commission_amount);
        $this->assertEquals(90.00, $earning->pharmacy_receivable);
        $this->assertEquals('percentage', $earning->commission_type);
    }

    /** @test */
    public function test_fixed_commission_calculation()
    {
        // Create commission template: Fixed $5
        $commission = PharmacyCommission::create([
            'title' => 'Fixed $5',
            'commission_type' => 'fixed',
            'commission_value' => 5.00,
            'status' => true,
        ]);

        // Create pharmacy with commission
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy 2',
            'phone' => '0987654321',
            'address' => 'Test Address 2',
            'commission_id' => $commission->id,
            'is_active' => true,
        ]);

        // Create order with $100 subtotal
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        // Calculate commission
        $earning = $this->commissionService->calculateCommission($order);

        // Assert amounts are correct
        // Commission: Fixed 5
        // Pharmacy receivable: 100 - 5 = 95
        $this->assertEquals(5.00, $earning->commission_amount);
        $this->assertEquals(95.00, $earning->pharmacy_receivable);
        $this->assertEquals('fixed', $earning->commission_type);
    }

    /** @test */
    public function test_no_commission_assigned_produces_no_earning()
    {
        // Create pharmacy with NO commission assigned
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy 3',
            'phone' => '5555555555',
            'address' => 'Test Address 3',
            'is_active' => true,
        ]);

        // Create order
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        // Calculate commission
        $earning = $this->commissionService->calculateCommission($order);

        // Assert no earning was created
        $this->assertNull($earning);
    }

    /** @test */
    public function test_duplicate_order_does_not_create_second_earning()
    {
        // Create commission
        $commission = PharmacyCommission::create([
            'title' => 'Test Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => true,
        ]);

        // Create pharmacy with commission
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy 4',
            'phone' => '6666666666',
            'address' => 'Test Address 4',
            'commission_id' => $commission->id,
            'is_active' => true,
        ]);

        // Create order
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        // Calculate commission twice
        $earning1 = $this->commissionService->calculateCommission($order);
        $earning2 = $this->commissionService->calculateCommission($order);

        // Assert only first earning was created, second returned null
        $this->assertNotNull($earning1);
        $this->assertNull($earning2);

        // Assert only one earning exists for this order
        $count = PharmacyCommissionEarning::where('order_id', $order->id)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function test_reversal_sets_status_to_cancelled()
    {
        // Create commission
        $commission = PharmacyCommission::create([
            'title' => 'Test Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => true,
        ]);

        // Create pharmacy
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy 5',
            'phone' => '7777777777',
            'address' => 'Test Address 5',
            'commission_id' => $commission->id,
            'is_active' => true,
        ]);

        // Create and calculate earning
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 100.00,
            'delivery_fee' => 5.00,
            'total' => 105.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        $earning = $this->commissionService->calculateCommission($order);
        $this->assertEquals('unpaid', $earning->status);

        // Reverse commission
        $this->commissionService->reverseCommission($order);

        // Reload earning and assert status changed
        $earning->refresh();
        $this->assertEquals('cancelled', $earning->status);
    }

    /** @test */
    public function test_zero_subtotal_produces_zero_amounts()
    {
        // Create commission
        $commission = PharmacyCommission::create([
            'title' => 'Test Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'status' => true,
        ]);

        // Create pharmacy
        $pharmacy = Pharmacy::create([
            'name' => 'Test Pharmacy 6',
            'phone' => '8888888888',
            'address' => 'Test Address 6',
            'commission_id' => $commission->id,
            'is_active' => true,
        ]);

        // Create order with $0 subtotal
        $order = PharmacyOrder::create([
            'pharmacy_id' => $pharmacy->id,
            'user_id' => $this->patient->id,
            'address_id' => null,
            'subtotal' => 0.00,
            'delivery_fee' => 5.00,
            'total' => 5.00,
            'payment_method' => 'cash_on_delivery',
            'status' => 'delivered',
        ]);

        // Calculate commission
        $earning = $this->commissionService->calculateCommission($order);

        // Assert amounts are zero
        $this->assertEquals(0.00, $earning->commission_amount);
        $this->assertEquals(0.00, $earning->pharmacy_receivable);
    }
}
