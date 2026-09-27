<?php

namespace Modules\DoctorVisit\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\DoctorVisit\Models\DoctorVisitRequest;
use Modules\DoctorVisit\Models\DoctorVisitStatusHistory;
use Modules\DoctorVisit\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Role;

class DoctorVisitApiTest extends BaseTest
{
    protected User $patient;

    protected User $admin;

    protected User $receptionist;

    protected User $doctor;

    protected User $anotherPatient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();

        // Create roles
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'], ['title' => 'User']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['title' => 'Admin']);
        Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web'], ['title' => 'Receptionist']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web'], ['title' => 'Doctor']);

        // Create test users
        $this->patient = User::factory()->create(['email' => 'patient@test.com']);
        $this->patient->assignRole('user');

        $this->anotherPatient = User::factory()->create(['email' => 'another@test.com']);
        $this->anotherPatient->assignRole('user');

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->receptionist = User::factory()->create(['email' => 'receptionist@test.com']);
        $this->receptionist->assignRole('receptionist');

        $this->doctor = User::factory()->create(['email' => 'doctor@test.com']);
        $this->doctor->assignRole('doctor');

        // Ensure Spatie's permission cache is fresh after all role assignments
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Reload roles on each user so hasRole() works off the fresh DB state
        $this->patient->load('roles');
        $this->anotherPatient->load('roles');
        $this->admin->load('roles');
        $this->receptionist->load('roles');
        $this->doctor->load('roles');
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'visit_reason'   => 'Persistent fever and headache',
            'preferred_date' => now()->addDays(3)->format('Y-m-d'),
            'contact_phone'  => '+966501234567',
        ], $overrides);
    }

    private function createDoctorRecord(User $user): int
    {
        return DB::table('doctors')->insertGetId([
            'doctor_id'  => $user->id,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createVisitRequest(User $patient, array $attrs = []): DoctorVisitRequest
    {
        return DoctorVisitRequest::create(array_merge([
            'patient_id'   => $patient->id,
            'visit_type'   => DoctorVisitRequest::VISIT_TYPE_HOME,
            'visit_reason' => 'Test reason',
            'preferred_date' => now()->addDays(5)->format('Y-m-d'),
            'contact_phone' => '+966500000000',
            'status'        => DoctorVisitRequest::STATUS_PENDING,
        ], $attrs));
    }

    // ══════════════════════════════════════════════════════════════
    // US1: Submit Doctor Visit Request
    // ══════════════════════════════════════════════════════════════

    public function test_authenticated_patient_can_submit_valid_request(): void
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'pending');

        $referenceNumber = $response->json('data.reference_number');
        $this->assertMatchesRegularExpression('/^VR-\d{4}-\d{4}$/', $referenceNumber);

        $this->assertDatabaseHas('doctor_visit_requests', [
            'patient_id' => $this->patient->id,
            'status'     => 'pending',
        ]);
    }

    public function test_patient_can_cancel_pending_request_with_reason(): void
    {
        $request = $this->createVisitRequest($this->patient, ['status' => DoctorVisitRequest::STATUS_PENDING]);

        $this->actingAs($this->patient)
            ->postJson("/api/v1/doctor-visit/requests/{$request->reference_number}/cancel", [
                'cancellation_reason' => 'My schedule changed',
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', DoctorVisitRequest::STATUS_CANCELLED);

        $this->assertDatabaseHas('doctor_visit_requests', [
            'id' => $request->id,
            'status' => DoctorVisitRequest::STATUS_CANCELLED,
            'cancellation_reason' => 'My schedule changed',
        ]);
        $this->assertDatabaseHas('doctor_visit_status_histories', [
            'visit_request_id' => $request->id,
            'old_status' => DoctorVisitRequest::STATUS_PENDING,
            'new_status' => DoctorVisitRequest::STATUS_CANCELLED,
            'changed_by' => $this->patient->id,
        ]);
    }

    public function test_patient_cannot_cancel_another_patients_request(): void
    {
        $request = $this->createVisitRequest($this->patient);

        $this->actingAs($this->anotherPatient)
            ->postJson("/api/v1/doctor-visit/requests/{$request->reference_number}/cancel", [
                'cancellation_reason' => 'Unauthorized attempt',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('doctor_visit_requests', [
            'id' => $request->id,
            'status' => DoctorVisitRequest::STATUS_PENDING,
        ]);
    }

    public function test_patient_cannot_cancel_confirmed_or_completed_request(): void
    {
        foreach ([DoctorVisitRequest::STATUS_CONFIRMED, DoctorVisitRequest::STATUS_COMPLETED] as $status) {
            $request = $this->createVisitRequest($this->patient, ['status' => $status]);

            $this->actingAs($this->patient)
                ->postJson("/api/v1/doctor-visit/requests/{$request->reference_number}/cancel", [
                    'cancellation_reason' => 'Too late',
                ])
                ->assertStatus(422)
                ->assertJsonPath('status', false);
        }
    }

    public function test_repeated_cancellation_returns_already_cancelled_without_a_second_history_entry(): void
    {
        $request = $this->createVisitRequest($this->patient);
        $endpoint = "/api/v1/doctor-visit/requests/{$request->reference_number}/cancel";
        $payload = ['cancellation_reason' => 'No longer needed'];

        $this->actingAs($this->patient)->postJson($endpoint, $payload)->assertOk();
        $this->actingAs($this->patient)
            ->postJson($endpoint, $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', __('doctor_visit.request_already_cancelled'));

        $this->assertSame(1, DoctorVisitStatusHistory::where('visit_request_id', $request->id)
            ->where('new_status', DoctorVisitRequest::STATUS_CANCELLED)
            ->count());
    }

    public function test_stale_patient_cancellation_is_rejected(): void
    {
        $request = $this->createVisitRequest($this->patient);
        $staleUpdatedAt = $request->updated_at->copy()->subSecond()->toISOString();

        $this->actingAs($this->patient)
            ->postJson("/api/v1/doctor-visit/requests/{$request->reference_number}/cancel", [
                'cancellation_reason' => 'Stale request',
                'expected_updated_at' => $staleUpdatedAt,
            ])
            ->assertStatus(409)
            ->assertJsonPath('status', false);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->postJson('/api/v1/doctor-visit/requests', $this->validPayload())
            ->assertStatus(401);
    }

    public function test_missing_visit_reason_returns_422(): void
    {
        $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload(['visit_reason' => '']))
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_past_preferred_date_returns_422(): void
    {
        $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload([
                'preferred_date' => now()->subDay()->format('Y-m-d'),
            ]))
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_all_required_fields_missing_returns_422_with_multiple_errors(): void
    {
        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonStructure(['errors']);

        $errors = $response->json('errors');
        $this->assertArrayHasKey('visit_reason', $errors);
        $this->assertArrayHasKey('preferred_date', $errors);
        $this->assertArrayHasKey('contact_phone', $errors);
    }

    public function test_request_with_inactive_preferred_doctor_is_accepted(): void
    {
        // Create a doctor record (inactive: status=0)
        $doctorId = $this->createDoctorRecord($this->doctor);
        DB::table('doctors')->where('id', $doctorId)->update(['status' => 0]);

        $response = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload([
                'preferred_doctor_id' => $doctorId,
            ]));

        // System warns but still accepts (per spec edge cases)
        $response->assertStatus(201)
            ->assertJsonPath('status', true);
    }

    public function test_sequential_submissions_get_consecutive_reference_numbers(): void
    {
        $r1 = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload())
            ->assertStatus(201)
            ->json('data.reference_number');

        $r2 = $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload())
            ->assertStatus(201)
            ->json('data.reference_number');

        $year = now()->format('Y');
        $n1 = (int) last(explode('-', $r1));
        $n2 = (int) last(explode('-', $r2));
        $this->assertEquals($n1 + 1, $n2, 'Reference numbers should be consecutive');
    }

    // ══════════════════════════════════════════════════════════════
    // US2: View Own Visit Requests
    // ══════════════════════════════════════════════════════════════

    public function test_patient_can_list_own_requests_newest_first(): void
    {
        $this->createVisitRequest($this->patient, ['visit_reason' => 'Older']);
        $this->createVisitRequest($this->patient, ['visit_reason' => 'Newer']);

        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctor-visit/requests')
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('Newer', $data[0]['visit_reason']);
    }

    public function test_list_returns_empty_array_when_no_requests(): void
    {
        $this->actingAs($this->patient)
            ->getJson('/api/v1/doctor-visit/requests')
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(0, 'data');
    }

    public function test_list_does_not_return_other_patients_requests(): void
    {
        $this->createVisitRequest($this->anotherPatient);

        $response = $this->actingAs($this->patient)
            ->getJson('/api/v1/doctor-visit/requests');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_show_returns_full_details_by_reference(): void
    {
        $request = $this->createVisitRequest($this->patient);
        $request->update(['admin_notes' => 'Internal note from admin']);

        $this->actingAs($this->patient)
            ->getJson("/api/v1/doctor-visit/requests/{$request->reference_number}")
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.reference_number', $request->reference_number)
            // Internal staff notes must not be exposed to the patient.
            ->assertJsonPath('data.admin_notes', null);
    }

    public function test_staff_can_read_internal_notes(): void
    {
        $request = $this->createVisitRequest($this->patient);
        $request->update(['admin_notes' => 'Internal note from admin']);

        $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/doctor-visit/requests')
            ->assertStatus(200)
            ->assertJsonPath('data.0.admin_notes', 'Internal note from admin');
    }

    public function test_show_returns_403_for_another_patients_request(): void
    {
        $request = $this->createVisitRequest($this->anotherPatient);

        $this->actingAs($this->patient)
            ->getJson("/api/v1/doctor-visit/requests/{$request->reference_number}")
            ->assertStatus(403);
    }

    public function test_show_returns_404_for_nonexistent_reference(): void
    {
        $this->actingAs($this->patient)
            ->getJson('/api/v1/doctor-visit/requests/VR-2099-9999')
            ->assertStatus(404);
    }

    // ══════════════════════════════════════════════════════════════
    // US3: Admin Views and Manages Visit Requests
    // ══════════════════════════════════════════════════════════════

    public function test_admin_can_list_all_requests_with_pagination(): void
    {
        // --- isolation probe ---
        $this->assertTrue(
            $this->admin->hasAnyRole(['admin', 'receptionist', 'doctor']),
            'PHP-level check: admin roles=' . json_encode($this->admin->roles->pluck('name'))
        );
        // ----------------------
        $this->createVisitRequest($this->patient);
        $this->createVisitRequest($this->anotherPatient);

        $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/doctor-visit/requests')
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }

    public function test_status_filter_returns_only_matching_requests(): void
    {
        $this->createVisitRequest($this->patient, ['status' => 'pending']);
        $this->createVisitRequest($this->anotherPatient, ['status' => 'confirmed']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/doctor-visit/requests?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_date_filters_work_correctly(): void
    {
        $this->createVisitRequest($this->patient, ['preferred_date' => '2026-04-10']);
        $this->createVisitRequest($this->anotherPatient, ['preferred_date' => '2026-04-20']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/doctor-visit/requests?date_from=2026-04-15&date_to=2026-04-25');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.preferred_date', '2026-04-20');
    }

    public function test_valid_status_transition_updates_status_and_creates_history(): void
    {
        $request = $this->createVisitRequest($this->patient, ['status' => 'pending']);

        $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
                'note'   => 'Confirmed by admin.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('doctor_visit_requests', [
            'id'     => $request->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('doctor_visit_status_histories', [
            'visit_request_id' => $request->id,
            'old_status'       => 'pending',
            'new_status'       => 'confirmed',
        ]);
    }

    public function test_invalid_status_transition_returns_422(): void
    {
        $request = $this->createVisitRequest($this->patient, ['status' => 'completed']);

        $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
            ])
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_terminal_state_completed_blocks_all_transitions(): void
    {
        $request = $this->createVisitRequest($this->patient, ['status' => 'completed']);

        foreach (['pending', 'confirmed', 'cancelled'] as $target) {
            $this->actingAs($this->admin)
                ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                    'status' => $target,
                ])
                ->assertStatus(422);
        }
    }

    public function test_non_assigned_doctor_cannot_update_status(): void
    {
        $request = $this->createVisitRequest($this->patient, ['status' => 'pending']);
        // assigned_doctor_id is null — doctor is not assigned

        $this->actingAs($this->doctor)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
            ])
            ->assertStatus(403);
    }

    public function test_assigned_doctor_can_update_status(): void
    {
        $doctorId = $this->createDoctorRecord($this->doctor);
        $request = $this->createVisitRequest($this->patient, [
            'status'             => 'pending',
            'assigned_doctor_id' => $doctorId,
        ]);

        $this->actingAs($this->doctor)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');
    }

    public function test_email_is_sent_when_status_changes_to_confirmed(): void
    {
        Mail::fake();

        $request = $this->createVisitRequest($this->patient, ['status' => 'pending']);

        $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
            ])
            ->assertStatus(200);

        Mail::assertSent(\App\Mail\DoctorVisitStatusChanged::class, function ($mail) {
            return $mail->hasTo($this->patient->email);
        });
    }

    public function test_email_is_not_sent_on_initial_creation(): void
    {
        Mail::fake();

        $this->actingAs($this->patient)
            ->postJson('/api/v1/doctor-visit/requests', $this->validPayload())
            ->assertStatus(201);

        Mail::assertNothingSent();
    }

    public function test_receptionist_can_assign_doctor(): void
    {
        $doctorId = $this->createDoctorRecord($this->doctor);
        $request = $this->createVisitRequest($this->patient);

        $this->actingAs($this->receptionist)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/assign-doctor", [
                'doctor_id' => $doctorId,
            ])
            ->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('doctor_visit_requests', [
            'id'                 => $request->id,
            'assigned_doctor_id' => $doctorId,
        ]);
    }

    public function test_patient_token_cannot_access_admin_endpoints(): void
    {
        $request = $this->createVisitRequest($this->patient);

        $this->actingAs($this->patient)
            ->getJson('/api/v1/admin/doctor-visit/requests')
            ->assertStatus(403);

        $this->actingAs($this->patient)
            ->putJson("/api/v1/admin/doctor-visit/requests/{$request->reference_number}/status", [
                'status' => 'confirmed',
            ])
            ->assertStatus(403);
    }
}
