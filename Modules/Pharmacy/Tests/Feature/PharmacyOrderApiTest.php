<?php

namespace Modules\Pharmacy\Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCartItem;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Pharmacy\Models\PharmacyInventory;
use Modules\Pharmacy\Models\PharmacyProduct;
use Modules\Pharmacy\Models\PharmacyOrder;
use Tests\TestCase;

class PharmacyOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_list_an_empty_prescription_history(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)
            ->getJson('/api/v1/pharmacy/prescriptions')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('meta.total', 0);
    }

    public function test_flutter_delivery_address_snapshot_can_place_cash_order(): void
    {
        $patient = User::factory()->create();
        $this->seedCart($patient);

        $payload = [
            'pharmacy_id' => $this->pharmacy->id,
            'delivery_address' => '12 Nile Street, Cairo',
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
        ];

        $response = $this->actingAs($patient)
            ->withHeader('Idempotency-Key', 'pharmacy-contract-1')
            ->postJson('/api/v1/pharmacy/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.delivery_address', '12 Nile Street, Cairo')
            ->assertJsonPath('data.payment_method', 'cash_on_delivery');

        $this->assertDatabaseHas('pharmacy_orders', [
            'user_id' => $patient->id,
            'delivery_address' => '12 Nile Street, Cairo',
            'payment_method' => 'cash_on_delivery',
            'status' => PharmacyOrder::STATUS_PENDING,
        ]);
    }

    public function test_same_pharmacy_order_key_replays_without_creating_a_second_order(): void
    {
        $patient = User::factory()->create();
        $this->seedCart($patient);

        $payload = [
            'pharmacy_id' => $this->pharmacy->id,
            'delivery_address' => '12 Nile Street, Cairo',
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
        ];

        $first = $this->actingAs($patient)
            ->withHeader('Idempotency-Key', 'pharmacy-contract-replay')
            ->postJson('/api/v1/pharmacy/orders', $payload);
        $second = $this->actingAs($patient)
            ->withHeader('Idempotency-Key', 'pharmacy-contract-replay')
            ->postJson('/api/v1/pharmacy/orders', $payload);

        $first->assertCreated();
        $second->assertCreated()->assertHeader('Idempotency-Replayed', 'true');
        $this->assertSame(1, PharmacyOrder::where('user_id', $patient->id)->count());
    }

    public function test_saved_address_must_belong_to_the_authenticated_patient(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();
        $foreignAddress = new Address([
            'address_line_1' => 'Foreign address',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'country' => 'Egypt',
            'latitude' => 30.0,
            'longitude' => 31.0,
        ]);
        $foreignAddress->addressable_id = $otherPatient->id;
        $foreignAddress->addressable_type = User::class;
        $foreignAddress->save();

        $response = $this->actingAs($patient)
            ->withHeader('Idempotency-Key', 'pharmacy-contract-owner-boundary')
            ->postJson('/api/v1/pharmacy/orders', [
                'pharmacy_id' => $this->pharmacy->id,
                'address_id' => $foreignAddress->id,
                'payment_method' => 'cash_on_delivery',
                'delivery_type' => 'delivery',
            ]);

        $response->assertStatus(422)->assertJsonPath('status', false);
        $this->assertDatabaseCount('pharmacy_orders', 0);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $category = PharmacyCategory::create([
            'name_ar' => 'أدوية',
            'name_en' => 'Medicine',
            'is_active' => true,
        ]);

        $this->pharmacy = Pharmacy::create([
            'name_ar' => 'صيدلية اختبار',
            'name_en' => 'Test Pharmacy',
            'name' => 'Test Pharmacy',
            'phone' => '0100000000',
            'address' => 'Cairo',
            'delivery_fee' => 0,
            'minimum_order_amount' => 0,
            'avg_preparation_time_min' => 30,
            'is_active' => true,
        ]);

        $this->category = $category;
    }

    protected function seedCart(User $patient): void
    {
        $product = PharmacyProduct::create([
            'name_ar' => 'دواء اختبار',
            'name_en' => 'Test Medicine',
            'category_id' => $this->category->id,
            'sub_category_id' => $this->category->id,
            'requires_prescription' => false,
            'is_active' => true,
        ]);

        PharmacyInventory::create([
            'pharmacy_id' => $this->pharmacy->id,
            'product_id' => $product->id,
            'price' => 25,
            'stock_qty' => 10,
            'is_available' => true,
        ]);

        PharmacyCartItem::create([
            'user_id' => $patient->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }
}
