<?php

namespace Modules\RequestNurse\Tests\Unit;

use App\Models\User;
use Carbon\Carbon;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;

class NurseRequestModelTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_nurse_request_belongs_to_patient()
    {
        $patient = User::factory()->create();
        $request = NurseRequest::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(User::class, $request->patient);
        $this->assertEquals($patient->id, $request->patient->id);
    }

    public function test_nurse_request_belongs_to_nurse()
    {
        $nurse = User::factory()->create();
        $request = NurseRequest::factory()->create(['nurse_id' => $nurse->id]);

        $this->assertInstanceOf(User::class, $request->nurse);
        $this->assertEquals($nurse->id, $request->nurse->id);
    }

    public function test_nurse_request_nurse_is_nullable()
    {
        $request = NurseRequest::factory()->create(['nurse_id' => null]);

        $this->assertNull($request->nurse);
    }

    public function test_nurse_request_belongs_to_nurse_profile()
    {
        $nurse = Nurse::factory()->create();
        $request = NurseRequest::factory()->create(['nurse_id' => $nurse->nurse_id]);

        $this->assertInstanceOf(Nurse::class, $request->nurseProfile);
        $this->assertEquals($nurse->id, $request->nurseProfile->id);
    }

    public function test_nurse_profile_returns_null_when_no_profile()
    {
        $user = User::factory()->create();
        $request = NurseRequest::factory()->create(['nurse_id' => $user->id]);

        $this->assertNull($request->nurseProfile);
    }

    public function test_full_address_accessor_all_fields()
    {
        $request = NurseRequest::factory()->create([
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'country' => 'USA',
        ]);

        $expected = '123 Main St, Apt 4B, Springfield, IL, 62701, USA';
        $this->assertEquals($expected, $request->full_address);
    }

    public function test_full_address_accessor_partial_fields()
    {
        $request = NurseRequest::factory()->create([
            'address_line_1' => '123 Main St',
            'address_line_2' => null,
            'city' => 'Springfield',
            'state' => null,
            'postal_code' => null,
            'country' => 'USA',
        ]);

        $expected = '123 Main St, Springfield, USA';
        $this->assertEquals($expected, $request->full_address);
    }

    public function test_full_address_accessor_all_null()
    {
        $request = NurseRequest::factory()->create();

        // Set address fields to null on the model to test the accessor
        $request->address_line_1 = null;
        $request->address_line_2 = null;
        $request->city = null;
        $request->state = null;
        $request->postal_code = null;
        $request->country = null;

        $this->assertEquals('', $request->full_address);
    }

    public function test_status_badge_accessor_pending()
    {
        $request = NurseRequest::factory()->create(['status' => 'pending']);

        $badge = $request->status_badge;
        $this->assertStringContainsString('bg-warning', $badge);
        $this->assertStringContainsString(__('request_nurse.status_pending'), $badge);
    }

    /**
     * @dataProvider statusBadgeDataProvider
     */
    public function test_status_badge_accessor_all_statuses($status, $expectedClass)
    {
        $request = NurseRequest::factory()->create(['status' => $status]);

        $badge = $request->status_badge;
        $this->assertStringContainsString($expectedClass, $badge);
        $this->assertStringContainsString(__("request_nurse.status_{$status}"), $badge);
    }

    public static function statusBadgeDataProvider()
    {
        return [
            ['pending', 'bg-warning'],
            ['confirmed', 'bg-info'],
            ['in_progress', 'bg-primary'],
            ['completed', 'bg-success'],
            ['cancelled', 'bg-danger'],
        ];
    }

    public function test_status_badge_accessor_unknown_status()
    {
        $request = NurseRequest::factory()->create(['status' => 'unknown_status']);

        $badge = $request->status_badge;
        $this->assertStringContainsString('bg-secondary', $badge);
        $this->assertStringContainsString('unknown_status', $badge);
    }

    public function test_pending_scope()
    {
        NurseRequest::factory()->count(3)->create(['status' => 'pending']);
        NurseRequest::factory()->count(2)->create(['status' => 'confirmed']);

        $pendingRequests = NurseRequest::pending()->get();
        $this->assertCount(3, $pendingRequests);
    }

    public function test_confirmed_scope()
    {
        NurseRequest::factory()->count(2)->create(['status' => 'confirmed']);
        NurseRequest::factory()->count(3)->create(['status' => 'pending']);

        $confirmedRequests = NurseRequest::confirmed()->get();
        $this->assertCount(2, $confirmedRequests);
    }

    public function test_by_nurse_scope()
    {
        $nurse1 = User::factory()->create();
        $nurse2 = User::factory()->create();

        NurseRequest::factory()->count(3)->create(['nurse_id' => $nurse1->id]);
        NurseRequest::factory()->count(2)->create(['nurse_id' => $nurse2->id]);

        $nurse1Requests = NurseRequest::byNurse($nurse1->id)->get();
        $this->assertCount(3, $nurse1Requests);
    }

    public function test_by_patient_scope()
    {
        $patient1 = User::factory()->create();
        $patient2 = User::factory()->create();

        NurseRequest::factory()->count(2)->create(['patient_id' => $patient1->id]);
        NurseRequest::factory()->count(3)->create(['patient_id' => $patient2->id]);

        $patient1Requests = NurseRequest::byPatient($patient1->id)->get();
        $this->assertCount(2, $patient1Requests);
    }

    /**
     * @dataProvider statusScopeDataProvider
     */
    public function test_by_status_scope($status, $otherStatus)
    {
        NurseRequest::factory()->count(2)->create(['status' => $status]);
        NurseRequest::factory()->count(3)->create(['status' => $otherStatus]);

        $statusRequests = NurseRequest::byStatus($status)->get();
        $this->assertCount(2, $statusRequests);
    }

    public static function statusScopeDataProvider()
    {
        return [
            ['pending', 'confirmed'],
            ['confirmed', 'pending'],
            ['in_progress', 'pending'],
            ['completed', 'pending'],
            ['cancelled', 'pending'],
        ];
    }

    public function test_scope_returns_empty_collection_when_no_matches()
    {
        $requests = NurseRequest::byStatus('non_existent_status')->get();
        $this->assertCount(0, $requests);
    }

    public function test_status_constants()
    {
        $this->assertEquals('pending', NurseRequest::STATUS_PENDING);
        $this->assertEquals('confirmed', NurseRequest::STATUS_CONFIRMED);
        $this->assertEquals('in_progress', NurseRequest::STATUS_IN_PROGRESS);
        $this->assertEquals('completed', NurseRequest::STATUS_COMPLETED);
        $this->assertEquals('cancelled', NurseRequest::STATUS_CANCELLED);
    }

    public function test_statuses_array_contains_all_statuses()
    {
        $expected = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        $this->assertEquals($expected, NurseRequest::STATUSES);
    }

    public function test_preferred_date_cast_to_carbon()
    {
        $request = NurseRequest::factory()->create();

        $this->assertInstanceOf(Carbon::class, $request->preferred_date);
    }

    public function test_payment_status_cast_to_boolean()
    {
        $request = NurseRequest::factory()->create(['payment_status' => 1]);

        $this->assertIsBool($request->payment_status);
        $this->assertTrue($request->payment_status);
    }

    public function test_total_amount_cast_to_double()
    {
        $request = NurseRequest::factory()->create(['total_amount' => '150']);

        $this->assertIsFloat($request->total_amount);
        $this->assertEquals(150.0, $request->total_amount);
    }

    public function test_duration_hours_cast_to_double()
    {
        $request = NurseRequest::factory()->create(['duration_hours' => '3']);

        $this->assertIsFloat($request->duration_hours);
        $this->assertEquals(3.0, $request->duration_hours);
    }
}
