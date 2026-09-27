<?php

namespace Modules\RadiologyLab\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Models\FacilitySession;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Modules\RadiologyLab\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FacilityBookingTest extends BaseTest
{

    protected string $monday;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'Modules/Location/database/migrations',
            '--force' => true,
        ]);

        $this->seedBookingPermissions();

        $this->monday = now()->next('Monday')->format('Y-m-d');
    }

    protected function seedBookingPermissions(): void
    {
        foreach (['view_facility_bookings', 'manage_facility_bookings'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['title' => 'Admin']);
        $adminRole->givePermissionTo(['view_facility_bookings', 'manage_facility_bookings']);
    }

    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    protected function createAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        // Pre-load roles via direct query so loadMissing() skips the broken eager-load path
        $user->setRelation('roles', $user->roles()->get());

        return $user;
    }

    protected function createLab(array $overrides = []): Lab
    {
        return Lab::create(array_merge([
            'name_ar' => 'مختبر تجريبي',
            'name_en' => 'Test Lab',
            'status' => 1,
        ], $overrides));
    }

    protected function createRadiologyCenter(array $overrides = []): RadiologyCenter
    {
        return RadiologyCenter::create(array_merge([
            'name_ar' => 'مركز أشعة تجريبي',
            'name_en' => 'Test Radiology Center',
            'scan_type' => 'X-Ray',
            'status' => 1,
        ], $overrides));
    }

    protected function createSession(string $type, int $facilityId, array $overrides = []): FacilitySession
    {
        return FacilitySession::create(array_merge([
            'facility_type' => $type,
            'facility_id' => $facilityId,
            'day' => 'monday',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'slot_duration' => 30,
            'is_holiday' => false,
            'status' => true,
        ], $overrides));
    }

    public function test_patient_can_create_lab_booking(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();
        $this->createSession('lab', $lab->id);

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'Ahmed Ali',
            'patient_phone' => '+201234567890',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => __('radiology_lab.booking_created'),
            ]);

        $this->assertDatabaseHas('facility_bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'status' => FacilityBooking::STATUS_PENDING,
        ]);
    }

    public function test_patient_can_create_radiology_booking_with_scan_type(): void
    {
        $patient = $this->createUser();
        $center = $this->createRadiologyCenter();
        $this->createSession('radiology', $center->id);

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'radiology',
            'radiology_center_id' => $center->id,
            'scan_type' => 'MRI Brain',
            'booking_date' => $this->monday,
            'booking_time' => '10:00',
            'patient_name' => 'Sara Ahmed',
            'patient_phone' => '+201111111111',
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('facility_bookings', [
            'type' => 'radiology',
            'radiology_center_id' => $center->id,
            'scan_type' => 'MRI Brain',
        ]);
    }

    public function test_booking_number_is_auto_generated(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();
        $this->createSession('lab', $lab->id);

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'Test Patient',
            'patient_phone' => '0500000000',
        ]);

        $response->assertStatus(201);
        $this->assertMatchesRegularExpression('/^BK-\d{4}-\d{4}$/', $response->json('data.booking_number'));
    }

    public function test_validation_rejects_missing_patient_name(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_phone' => '0500000000',
            // patient_name missing
        ]);

        $response->assertStatus(422);
    }

    public function test_validation_rejects_past_date(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => Carbon::yesterday()->format('Y-m-d'),
            'booking_time' => '09:00',
            'patient_name' => 'Test',
            'patient_phone' => '0500000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_double_booking_returns_error(): void
    {
        $patient1 = $this->createUser();
        $patient2 = $this->createUser(['email' => 'patient2@example.com']);
        $lab = $this->createLab();
        $this->createSession('lab', $lab->id);

        $this->actingAs($patient1)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'First Patient',
            'patient_phone' => '0500000001',
        ])->assertStatus(201);

        $response = $this->actingAs($patient2)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'Second Patient',
            'patient_phone' => '0500000002',
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    public function test_patient_can_list_own_bookings(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();

        FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '10:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->actingAs($patient)->getJson('/api/v1/facility-bookings');

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_patient_cannot_see_other_patients_booking(): void
    {
        $patient1 = $this->createUser();
        $patient2 = $this->createUser(['email' => 'p2@example.com']);
        $lab = $this->createLab();

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient1->id,
            'patient_name' => 'Patient 1',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->actingAs($patient2)->getJson("/api/v1/facility-bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    public function test_patient_can_cancel_pending_booking(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->actingAs($patient)->postJson("/api/v1/facility-bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Changed my mind',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('radiology_lab.booking_cancelled'),
            ]);

        $this->assertDatabaseHas('facility_bookings', [
            'id' => $booking->id,
            'status' => FacilityBooking::STATUS_CANCELLED,
            'cancelled_by' => (string) $patient->id,
        ]);
    }

    public function test_patient_cannot_cancel_completed_booking(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab();

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($patient)->postJson("/api/v1/facility-bookings/{$booking->id}/cancel");

        $response->assertStatus(422);
    }

    public function test_non_authenticated_user_gets_401(): void
    {
        $response = $this->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => 1,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'Test',
            'patient_phone' => '050',
        ]);

        $response->assertStatus(401);
    }

    public function test_inactive_facility_is_rejected(): void
    {
        $patient = $this->createUser();
        $lab = $this->createLab(['status' => 0]);
        $this->createSession('lab', $lab->id);

        $response = $this->actingAs($patient)->postJson('/api/v1/facility-bookings', [
            'type' => 'lab',
            'lab_id' => $lab->id,
            'booking_date' => $this->monday,
            'booking_time' => '09:00',
            'patient_name' => 'Ahmed',
            'patient_phone' => '0500000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_confirm_pending_booking(): void
    {
        $admin = $this->createAdminUser();
        $lab = $this->createLab();
        $patient = $this->createUser(['email' => 'patient@example.com']);

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/facility-bookings/{$booking->id}/confirm", [
            'admin_notes' => 'Confirmed by admin',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('radiology_lab.booking_confirmed'),
            ]);

        $this->assertDatabaseHas('facility_bookings', [
            'id' => $booking->id,
            'status' => FacilityBooking::STATUS_CONFIRMED,
        ]);
    }

    public function test_admin_can_cancel_booking_with_reason(): void
    {
        $admin = $this->createAdminUser();
        $lab = $this->createLab();
        $patient = $this->createUser(['email' => 'patient@example.com']);

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/facility-bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Facility not available',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('facility_bookings', [
            'id' => $booking->id,
            'status' => FacilityBooking::STATUS_CANCELLED,
            'cancelled_by' => (string) $admin->id,
        ]);
    }

    public function test_admin_can_mark_booking_completed(): void
    {
        $admin = $this->createAdminUser();
        $lab = $this->createLab();
        $patient = $this->createUser(['email' => 'patient@example.com']);

        $booking = FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Ahmed',
            'patient_phone' => '050000',
            'booking_date' => $this->monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/facility-bookings/{$booking->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => __('radiology_lab.booking_completed'),
            ]);
    }

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $patient = $this->createUser();

        $response = $this->actingAs($patient)->getJson('/api/v1/admin/facility-bookings');

        $response->assertStatus(403);
    }
}
