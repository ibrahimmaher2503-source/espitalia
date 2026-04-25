<?php

namespace Modules\RequestNurse\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Modules\RequestNurse\Http\Requests\NurseFormRequest;

class NurseFormRequestTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_valid_data_passes_validation()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_first_name_is_required()
    {
        $request = new NurseFormRequest;
        $data = [
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    public function test_last_name_is_required()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    public function test_email_is_required()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'hourly_rate' => 50.00,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_must_be_valid_format()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'not-an-email',
            'hourly_rate' => 50.00,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_hourly_rate_is_required()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('hourly_rate', $validator->errors()->toArray());
    }

    public function test_hourly_rate_must_be_non_negative()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => -5,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('hourly_rate', $validator->errors()->toArray());
    }

    public function test_hourly_rate_allows_zero()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 0,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function test_availability_status_must_be_valid_enum()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
            'availability_status' => 'invalid',
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('availability_status', $validator->errors()->toArray());
    }

    /**
     * @dataProvider availabilityStatusDataProvider
     */
    public function test_availability_status_accepts_valid_values($status)
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
            'availability_status' => $status,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public static function availabilityStatusDataProvider()
    {
        return [
            ['available'],
            ['busy'],
            ['off_duty'],
        ];
    }

    public function test_about_max_length_2000()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
            'about_en' => str_repeat('a', 2001),
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('about_en', $validator->errors()->toArray());
    }

    public function test_optional_fields_can_be_null()
    {
        $request = new NurseFormRequest;
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'hourly_rate' => 50.00,
            'mobile' => null,
            'specialization_ar' => null,
            'specialization_en' => null,
            'experience_ar' => null,
            'experience_en' => null,
            'about_ar' => null,
            'about_en' => null,
            'service_area_ar' => null,
            'service_area_en' => null,
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }
}
