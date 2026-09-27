<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\IdempotencyRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Clinic\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Role;

class IdempotencyMiddlewareTest extends BaseTest
{
    private User $patient;

    private int $executions = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'], ['title' => 'User']);
        $this->patient = User::factory()->create();
        $this->patient->assignRole('user');

        Route::post('/testing/idempotency-probe', function (Request $request) {
            $this->executions++;

            return response()->json([
                'status' => true,
                'data' => ['value' => $request->input('value'), 'execution' => $this->executions],
            ], 201);
        })->middleware(['auth:sanctum', 'idempotency:test_operation']);
    }

    public function test_same_key_replays_the_original_success_without_reexecuting(): void
    {
        $headers = ['Idempotency-Key' => 'probe-success-1', 'X-Request-ID' => 'request-1'];

        $first = $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'alpha']);
        $second = $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'alpha']);

        $first->assertCreated()->assertJsonPath('data.execution', 1);
        $second->assertCreated()
            ->assertHeader('Idempotency-Replayed', 'true')
            ->assertJsonPath('data.execution', 1);
        $this->assertSame(1, $this->executions);
        $this->assertDatabaseCount('idempotency_records', 1);
    }

    public function test_same_key_with_a_different_payload_is_rejected(): void
    {
        $headers = ['Idempotency-Key' => 'probe-payload-1'];

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'alpha'])
            ->assertCreated();

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'beta'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'idempotency_key_reused');

        $this->assertSame(1, $this->executions);
    }

    public function test_same_key_is_scoped_to_the_authenticated_user(): void
    {
        $anotherPatient = User::factory()->create();
        $anotherPatient->assignRole('user');
        $headers = ['Idempotency-Key' => 'user-scoped-key'];

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'patient-one'])
            ->assertCreated();

        $this->actingAs($anotherPatient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'patient-two'])
            ->assertCreated();

        $this->assertSame(2, $this->executions);
        $this->assertDatabaseCount('idempotency_records', 2);
    }

    public function test_status_lookup_reconciles_a_committed_result_after_a_lost_response(): void
    {
        $headers = ['Idempotency-Key' => 'probe-reconcile-1'];

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'committed'])
            ->assertCreated();

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->getJson('/api/v1/idempotency/status?operation=test_operation')
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.result.data.value', 'committed');
    }

    public function test_status_lookup_cannot_read_another_users_record(): void
    {
        $headers = ['Idempotency-Key' => 'probe-boundary-1'];

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders($headers)
            ->postJson('/testing/idempotency-probe', ['value' => 'private'])
            ->assertCreated();

        $anotherPatient = User::factory()->create();
        $anotherPatient->assignRole('user');

        $this->actingAs($anotherPatient, 'sanctum')
            ->withHeaders($headers)
            ->getJson('/api/v1/idempotency/status?operation=test_operation')
            ->assertNotFound();
    }

    public function test_missing_key_is_rejected_outside_the_legacy_testing_fallback(): void
    {
        // The production middleware contract is represented by the explicit
        // invalid-key branch; the testing fallback is intentionally limited to
        // this test environment so legacy feature tests stay isolated.
        app()->detectEnvironment(fn () => 'production');
        $this->assertTrue(app()->environment('production'));

        $this->actingAs($this->patient, 'sanctum')
            ->postJson('/testing/idempotency-probe', ['value' => 'missing-key'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'invalid_idempotency_key');
    }

    public function test_existing_processing_reservation_returns_pending_without_reexecuting(): void
    {
        $key = 'probe-processing-1';
        $requestHash = hash('sha256', json_encode([
            'method' => 'POST',
            'path' => 'testing/idempotency-probe',
            'query' => [],
            'input' => ['value' => 'waiting'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        IdempotencyRecord::create([
            'user_id' => $this->patient->id,
            'operation' => 'test_operation',
            'key_hash' => hash('sha256', $key),
            'request_hash' => $requestHash,
            'request_id' => 'request-processing-1',
            'endpoint' => 'POST testing/idempotency-probe',
            'status' => IdempotencyRecord::STATUS_PROCESSING,
            'expires_at' => now()->addHour(),
        ]);

        $this->actingAs($this->patient)
            ->withHeaders(['Idempotency-Key' => $key])
            ->postJson('/testing/idempotency-probe', ['value' => 'waiting'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'operation_pending')
            ->assertHeader('Retry-After', '2');

        $this->assertSame(0, $this->executions);
    }

    public function test_status_lookup_hides_a_result_after_the_replay_window_expires(): void
    {
        $key = 'probe-expired-1';
        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders(['Idempotency-Key' => $key])
            ->postJson('/testing/idempotency-probe', ['value' => 'expired'])
            ->assertCreated();

        IdempotencyRecord::query()
            ->where('user_id', $this->patient->id)
            ->where('operation', 'test_operation')
            ->update(['expires_at' => now()->subMinute()]);

        $this->actingAs($this->patient, 'sanctum')
            ->withHeaders(['Idempotency-Key' => $key])
            ->getJson('/api/v1/idempotency/status?operation=test_operation')
            ->assertOk()
            ->assertJsonPath('data.status', 'expired')
            ->assertJsonPath('data.result', null);
    }
}
