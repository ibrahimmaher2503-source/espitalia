<?php

namespace Modules\RequestNurse\Tests\Feature;

use App\Models\User;
use Modules\RequestNurse\Models\NurseRequest;
use Modules\RequestNurse\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Role;

class NurseRequestApiTest extends BaseTest
{
    private User $patient;

    private User $anotherPatient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'], ['title' => 'User']);

        $this->patient = User::factory()->create();
        $this->patient->assignRole('user');

        $this->anotherPatient = User::factory()->create();
        $this->anotherPatient->assignRole('user');

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->patient->load('roles');
        $this->anotherPatient->load('roles');
    }

    private function createRequest(User $patient, array $attributes = []): NurseRequest
    {
        return NurseRequest::factory()->create(array_merge([
            'patient_id' => $patient->id,
            'status' => NurseRequest::STATUS_PENDING,
        ], $attributes));
    }

    public function test_patient_can_cancel_pending_request_with_reason(): void
    {
        $request = $this->createRequest($this->patient);

        $this->actingAs($this->patient)
            ->postJson("/api/v1/nurse-requests/{$request->id}/cancel", [
                'cancellation_reason' => 'Plans changed',
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', NurseRequest::STATUS_CANCELLED);

        $this->assertDatabaseHas('nurse_requests', [
            'id' => $request->id,
            'status' => NurseRequest::STATUS_CANCELLED,
            'cancelled_by' => 'patient',
            'cancellation_reason' => 'Plans changed',
        ]);
    }

    public function test_patient_cannot_cancel_another_patients_request(): void
    {
        $request = $this->createRequest($this->patient);

        $this->actingAs($this->anotherPatient)
            ->postJson("/api/v1/nurse-requests/{$request->id}/cancel", [
                'cancellation_reason' => 'Unauthorized attempt',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('nurse_requests', [
            'id' => $request->id,
            'status' => NurseRequest::STATUS_PENDING,
        ]);
    }

    public function test_patient_cannot_cancel_confirmed_or_completed_request(): void
    {
        foreach ([NurseRequest::STATUS_CONFIRMED, NurseRequest::STATUS_COMPLETED] as $status) {
            $request = $this->createRequest($this->patient, ['status' => $status]);

            $this->actingAs($this->patient)
                ->postJson("/api/v1/nurse-requests/{$request->id}/cancel", [
                    'cancellation_reason' => 'Too late',
                ])
                ->assertStatus(422)
                ->assertJsonPath('status', false);
        }
    }

    public function test_same_cancellation_key_replays_without_a_second_mutation(): void
    {
        $request = $this->createRequest($this->patient);
        $endpoint = "/api/v1/nurse-requests/{$request->id}/cancel";
        $headers = ['Idempotency-Key' => 'nurse-cancel-replay-1'];
        $payload = ['cancellation_reason' => 'No longer needed'];

        $this->actingAs($this->patient)
            ->withHeaders($headers)
            ->postJson($endpoint, $payload)
            ->assertOk()
            ->assertHeader('Idempotency-Replayed', 'false');

        $this->actingAs($this->patient)
            ->withHeaders($headers)
            ->postJson($endpoint, $payload)
            ->assertOk()
            ->assertHeader('Idempotency-Replayed', 'true');

        $this->assertSame(1, NurseRequest::whereKey($request->id)
            ->where('status', NurseRequest::STATUS_CANCELLED)
            ->count());
        $this->assertDatabaseCount('idempotency_records', 1);
    }
}
