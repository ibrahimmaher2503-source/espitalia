<?php
namespace Modules\Clinic\Tests\Unit;

use Modules\Clinic\Models\DoctorIndependentService;

class DoctorIndependentServiceTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_fillable_attributes()
    {
        $service = new DoctorIndependentService();
        $expected = [
            'doctor_id', 'name', 'description', 'duration_min', 'time_slot',
            'charges', 'discount', 'discount_type', 'discount_value',
            'is_inclusive_tax', 'inclusive_tax', 'inclusive_tax_price', 'status',
        ];
        $this->assertEquals($expected, $service->getFillable());
    }

    public function test_charges_rounded_to_two_decimals()
    {
        $user = \App\Models\User::factory()->create();
        $service = DoctorIndependentService::create([
            'doctor_id' => $user->id,
            'name' => 'Test Service',
            'charges' => 99.999,
            'status' => 1,
        ]);
        $this->assertEquals(100.00, $service->charges);
    }

    public function test_can_create_service()
    {
        $user = \App\Models\User::factory()->create();
        $service = DoctorIndependentService::create([
            'doctor_id' => $user->id,
            'name' => 'General Consultation',
            'description' => 'Basic health checkup',
            'duration_min' => 30,
            'time_slot' => 15,
            'charges' => 50.00,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('doctor_independent_services', [
            'name' => 'General Consultation',
            'doctor_id' => $user->id,
        ]);
    }

    public function test_doctor_relationship()
    {
        $user = \App\Models\User::factory()->create();
        $service = DoctorIndependentService::create([
            'doctor_id' => $user->id,
            'name' => 'Test',
            'charges' => 10,
            'status' => 1,
        ]);
        $this->assertEquals($user->id, $service->doctor->id);
    }
}
