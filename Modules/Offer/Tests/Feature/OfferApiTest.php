<?php

namespace Modules\Offer\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Offer\Models\Offer;
use Modules\Offer\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OfferApiTest extends BaseTest
{
    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
        $this->admin = $this->createAdmin();
        $this->customer = $this->createCustomer();
    }

    protected function seedRolesAndPermissions(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        foreach (['view_offers', 'add_offers', 'edit_offers', 'delete_offers'] as $perm) {
            $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            $adminRole->givePermissionTo($p);
        }
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function createCustomer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        return $user;
    }

    protected function createOffer(array $overrides = []): Offer
    {
        return Offer::create(array_merge([
            'title_en' => 'Test Offer',
            'title_ar' => 'عرض تجريبي',
            'slug' => 'test-offer-' . uniqid(),
            'discount_type' => Offer::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'status' => Offer::STATUS_ACTIVE,
            'is_auto_apply' => false,
            'requires_coupon' => true,
            'coupon_code' => 'TEST10',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'priority' => 1,
            'is_stackable' => false,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    // ── Admin CRUD ─────────────────────────────────────────

    public function test_admin_can_list_offers()
    {
        $this->createOffer();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/offers');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_admin_can_create_offer()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/offers', [
                'title_en' => 'New Offer',
                'title_ar' => 'عرض جديد',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'priority' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJson(['status' => true]);
    }

    public function test_admin_can_show_offer()
    {
        $offer = $this->createOffer();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/offers/{$offer->id}");

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    public function test_admin_can_update_offer()
    {
        $offer = $this->createOffer();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/offers/{$offer->id}", [
                'title_en' => 'Updated Offer',
                'title_ar' => 'عرض محدث',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'priority' => 1,
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    public function test_admin_can_delete_offer()
    {
        $offer = $this->createOffer();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/offers/{$offer->id}");

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertSoftDeleted('offers', ['id' => $offer->id]);
    }

    // ── Authorization ─────────────────────────────────────

    public function test_customer_cannot_access_admin_crud()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/v1/offers');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_admin_crud()
    {
        $response = $this->getJson('/api/v1/offers');

        $response->assertStatus(401);
    }

    // ── Public Endpoints ─────────────────────────────────

    public function test_public_can_list_active_offers()
    {
        $this->createOffer(['is_auto_apply' => true, 'requires_coupon' => false, 'coupon_code' => null]);

        $response = $this->getJson('/api/v1/offers/public');

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    // ── Coupon Validation ────────────────────────────────

    public function test_validate_valid_coupon()
    {
        $this->createOffer(['coupon_code' => 'VALID20']);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/offers/validate-coupon', [
                'coupon_code' => 'VALID20',
                'service_charge' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }

    public function test_validate_invalid_coupon()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/offers/validate-coupon', [
                'coupon_code' => 'NONEXISTENT',
                'service_charge' => 100,
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'data' => ['valid' => false]]);
    }

    // ── Discount Calculation ─────────────────────────────

    public function test_calculate_discount_with_coupon()
    {
        $this->createOffer([
            'coupon_code' => 'CALC10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson('/api/v1/offers/calculate-discount', [
                'service_charge' => 200,
                'coupon_code' => 'CALC10',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }
}
