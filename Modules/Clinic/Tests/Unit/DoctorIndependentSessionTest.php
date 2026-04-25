<?php
namespace Modules\Clinic\Tests\Unit;

use Modules\Clinic\Models\DoctorIndependentSession;

class DoctorIndependentSessionTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_fillable_attributes()
    {
        $session = new DoctorIndependentSession();
        $expected = ['doctor_id', 'day', 'start_time', 'end_time', 'is_holiday', 'breaks', 'status'];
        $this->assertEquals($expected, $session->getFillable());
    }

    public function test_breaks_cast_to_array()
    {
        $session = new DoctorIndependentSession();
        $casts = $session->getCasts();
        $this->assertEquals('array', $casts['breaks']);
    }

    public function test_table_name()
    {
        $session = new DoctorIndependentSession();
        $this->assertEquals('doctor_independent_sessions', $session->getTable());
    }

    public function test_can_create_session()
    {
        $user = \App\Models\User::factory()->create();
        $session = DoctorIndependentSession::create([
            'doctor_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_holiday' => 0,
            'breaks' => [['start_break' => '12:00', 'end_break' => '13:00']],
            'status' => 1,
        ]);
        $this->assertDatabaseHas('doctor_independent_sessions', [
            'doctor_id' => $user->id,
            'day' => 'Monday',
        ]);
        $this->assertIsArray($session->breaks);
    }

    public function test_doctor_relationship()
    {
        $user = \App\Models\User::factory()->create();
        $session = DoctorIndependentSession::create([
            'doctor_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'status' => 1,
        ]);
        $this->assertEquals($user->id, $session->doctor->id);
    }
}
