<?php

namespace Modules\RequestNurse\Tests\Unit;

use Carbon\Carbon;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;
use Modules\RequestNurse\Transformers\NurseRequestResource;

class NurseRequestResourceTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_nurse_request_resource_contains_all_expected_keys()
    {
        $request = NurseRequest::factory()->create();
        $request->load(['patient', 'nurse', 'nurseProfile']);

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $expectedKeys = [
            'id',
            'patient',
            'nurse',
            'service_description',
            'request_date',
            'preferred_date',
            'preferred_time',
            'duration_hours',
            'address',
            'contact_number',
            'status',
            'payment_status',
            'total_amount',
            'patient_notes',
            'admin_notes',
            'cancelled_by',
            'cancellation_reason',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    public function test_patient_nested_object_structure()
    {
        $request = NurseRequest::factory()->create();
        $request->load('patient');

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertIsArray($array['patient']);
        $this->assertArrayHasKey('id', $array['patient']);
        $this->assertArrayHasKey('name', $array['patient']);
        $this->assertArrayHasKey('email', $array['patient']);
        $this->assertArrayHasKey('mobile', $array['patient']);
    }

    public function test_nurse_nested_object_structure()
    {
        $nurse = Nurse::factory()->create();
        $request = NurseRequest::factory()->create(['nurse_id' => $nurse->nurse_id]);
        $request->load(['nurse', 'nurseProfile']);

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertIsArray($array['nurse']);
        $this->assertArrayHasKey('id', $array['nurse']);
        $this->assertArrayHasKey('name', $array['nurse']);
        $this->assertArrayHasKey('specialization', $array['nurse']);
    }

    public function test_nurse_is_null_when_unassigned()
    {
        $request = NurseRequest::factory()->create(['nurse_id' => null]);
        $request->load('nurse');

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertNull($array['nurse']);
    }

    public function test_address_nested_object_structure()
    {
        $request = NurseRequest::factory()->create();

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertIsArray($array['address']);
        $expectedAddressKeys = [
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'country',
            'postal_code',
            'latitude',
            'longitude',
            'full_address',
        ];

        foreach ($expectedAddressKeys as $key) {
            $this->assertArrayHasKey($key, $array['address']);
        }
    }

    public function test_address_full_address_excludes_null_parts()
    {
        $request = NurseRequest::factory()->create([
            'address_line_1' => '123 Main St',
            'address_line_2' => null,
            'city' => 'Springfield',
            'state' => null,
            'postal_code' => null,
            'country' => 'USA',
        ]);

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $expected = '123 Main St, Springfield, USA';
        $this->assertEquals($expected, $array['address']['full_address']);
    }

    public function test_preferred_date_formatted_as_y_m_d()
    {
        $request = NurseRequest::factory()->create([
            'preferred_date' => Carbon::parse('2026-03-15 14:30:00'),
        ]);

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertEquals('2026-03-15', $array['preferred_date']);
    }

    public function test_payment_status_is_boolean()
    {
        $request = NurseRequest::factory()->create(['payment_status' => 1]);

        $resource = new NurseRequestResource($request);
        $array = $resource->toArray(request());

        $this->assertIsBool($array['payment_status']);
        $this->assertTrue($array['payment_status']);
    }
}
