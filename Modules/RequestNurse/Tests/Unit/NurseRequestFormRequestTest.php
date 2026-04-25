<?php

namespace Modules\RequestNurse\Tests\Unit;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Modules\RequestNurse\Http\Requests\NurseRequestFormRequest;

class NurseRequestFormRequestTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_valid_data_passes_validation()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care for elderly patient',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_patient_id_is_required()
    {
        $request = new NurseRequestFormRequest;
        $data = [
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('patient_id', $validator->errors()->toArray());
    }

    public function test_patient_id_must_exist_in_users()
    {
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => 99999,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('patient_id', $validator->errors()->toArray());
    }

    public function test_service_description_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('service_description_en', $validator->errors()->toArray());
    }

    public function test_service_description_max_1000()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => str_repeat('a', 1001),
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('service_description_en', $validator->errors()->toArray());
    }

    public function test_preferred_date_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('preferred_date', $validator->errors()->toArray());
    }

    public function test_preferred_date_must_be_today_or_future()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('preferred_date', $validator->errors()->toArray());
    }

    public function test_preferred_date_accepts_today()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_preferred_time_must_be_valid_format()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'preferred_time' => '25:00',
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('preferred_time', $validator->errors()->toArray());
    }

    public function test_preferred_time_accepts_valid_format()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'preferred_time' => '14:30',
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_duration_hours_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration_hours', $validator->errors()->toArray());
    }

    public function test_duration_hours_min_1()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 0,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration_hours', $validator->errors()->toArray());
    }

    public function test_duration_hours_max_24()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 25,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('duration_hours', $validator->errors()->toArray());
    }

    public function test_address_line_1_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('address_line_1', $validator->errors()->toArray());
    }

    public function test_city_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('city', $validator->errors()->toArray());
    }

    public function test_contact_number_is_required()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('contact_number', $validator->errors()->toArray());
    }

    public function test_nurse_id_must_exist_if_provided()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'nurse_id' => 99999,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('nurse_id', $validator->errors()->toArray());
    }

    public function test_optional_fields_can_be_null()
    {
        $patient = User::factory()->create();
        $request = new NurseRequestFormRequest;
        $data = [
            'patient_id' => $patient->id,
            'service_description_en' => 'Need nursing care',
            'preferred_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'duration_hours' => 4,
            'address_line_1' => '123 Main St',
            'city' => 'Springfield',
            'contact_number' => '555-1234',
            'preferred_time' => null,
            'address_line_2' => null,
            'state' => null,
            'country' => null,
            'postal_code' => null,
            'nurse_id' => null,
            'patient_notes' => null,
            'admin_notes' => null,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }
}
