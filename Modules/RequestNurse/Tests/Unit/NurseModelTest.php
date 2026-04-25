<?php

namespace Modules\RequestNurse\Tests\Unit;

use App\Models\User;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;

class NurseModelTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_nurse_belongs_to_user()
    {
        $user = User::factory()->create();
        $nurse = Nurse::factory()->create(['nurse_id' => $user->id]);

        $this->assertInstanceOf(User::class, $nurse->user);
        $this->assertEquals($user->id, $nurse->user->id);
    }

    public function test_nurse_belongs_to_vendor()
    {
        $vendor = User::factory()->create();
        $nurse = Nurse::factory()->create(['vendor_id' => $vendor->id]);

        $this->assertInstanceOf(User::class, $nurse->vendor);
        $this->assertEquals($vendor->id, $nurse->vendor->id);
    }

    public function test_nurse_vendor_is_nullable()
    {
        $nurse = Nurse::factory()->create(['vendor_id' => null]);

        $this->assertNull($nurse->vendor);
    }

    public function test_nurse_has_many_nurse_requests()
    {
        $nurse = Nurse::factory()->create();
        NurseRequest::factory()->count(3)->create(['nurse_id' => $nurse->nurse_id]);

        $this->assertCount(3, $nurse->nurseRequests);
    }

    public function test_full_name_accessor_returns_user_full_name()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $nurse = Nurse::factory()->create(['nurse_id' => $user->id]);

        $this->assertEquals('John Doe', $nurse->full_name);
    }

    public function test_full_name_accessor_returns_empty_when_no_user()
    {
        $nurse = Nurse::factory()->create(['nurse_id' => 99999]);

        $this->assertEquals('', $nurse->full_name);
    }

    public function test_available_scope_filters_correctly()
    {
        Nurse::factory()->create([
            'availability_status' => 'available',
            'status' => 1,
        ]);
        Nurse::factory()->create([
            'availability_status' => 'busy',
            'status' => 1,
        ]);
        Nurse::factory()->create([
            'availability_status' => 'available',
            'status' => 0,
        ]);

        $availableNurses = Nurse::available()->get();
        $this->assertCount(1, $availableNurses);
        $this->assertEquals('available', $availableNurses->first()->availability_status);
        $this->assertEquals(1, $availableNurses->first()->status);
    }

    public function test_active_scope_filters_correctly()
    {
        Nurse::factory()->count(3)->create(['status' => 1]);
        Nurse::factory()->count(2)->create(['status' => 0]);

        $activeNurses = Nurse::active()->get();
        $this->assertCount(3, $activeNurses);
        $activeNurses->each(function ($nurse) {
            $this->assertEquals(1, $nurse->status);
        });
    }

    public function test_hourly_rate_cast_to_double()
    {
        $nurse = Nurse::factory()->create(['hourly_rate' => '50']);

        $this->assertIsFloat($nurse->hourly_rate);
        $this->assertEquals(50.0, $nurse->hourly_rate);
    }

    public function test_status_cast_to_integer()
    {
        $nurse = Nurse::factory()->create(['status' => '1']);

        $this->assertIsInt($nurse->status);
        $this->assertEquals(1, $nurse->status);
    }

    public function test_fillable_fields()
    {
        $nurse = new Nurse;
        $expectedFillable = [
            'nurse_id',
            'vendor_id',
            'governorate_id',
            'city_id',
            'specialization_ar',
            'specialization_en',
            'experience_ar',
            'experience_en',
            'about_ar',
            'about_en',
            'availability_status',
            'service_area_ar',
            'service_area_en',
            'hourly_rate',
            'status',
        ];

        $this->assertEquals($expectedFillable, $nurse->getFillable());
    }
}
