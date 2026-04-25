<?php

namespace Modules\Specialization\Tests\Feature;

use App\Models\User;
use Modules\Clinic\Models\Doctor;
use Modules\Specialization\Models\Specialization;

class DoctorSpecializationTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
        $this->createMinimalDoctorsTable();
    }

    protected function createDoctorWithUser(): Doctor
    {
        $user = User::create([
            'first_name' => 'Dr',
            'last_name' => 'Test',
            'email' => 'doctor'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'doctor',
        ]);

        return Doctor::create([
            'doctor_id' => $user->id,
            'status' => 1,
        ]);
    }

    public function test_doctor_has_specializations_relationship(): void
    {
        $doctor = $this->createDoctorWithUser();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $doctor->specializations());
    }

    public function test_sync_specializations_on_doctor(): void
    {
        $doctor = $this->createDoctorWithUser();

        $spec1 = Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);
        $spec2 = Specialization::create(['name_en' => 'Neurology', 'status' => 1]);

        $doctor->specializations()->sync([$spec1->id, $spec2->id]);

        $this->assertCount(2, $doctor->fresh()->specializations);
    }

    public function test_replace_specializations(): void
    {
        $doctor = $this->createDoctorWithUser();

        $spec1 = Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);
        $spec2 = Specialization::create(['name_en' => 'Neurology', 'status' => 1]);
        $spec3 = Specialization::create(['name_en' => 'Orthopedics', 'status' => 1]);

        $doctor->specializations()->sync([$spec1->id, $spec2->id]);
        $doctor->specializations()->sync([$spec3->id]);

        $fresh = $doctor->fresh()->specializations;
        $this->assertCount(1, $fresh);
        $this->assertTrue($fresh->contains($spec3));
    }

    public function test_remove_all_specializations(): void
    {
        $doctor = $this->createDoctorWithUser();

        $spec = Specialization::create(['name_en' => 'Cardiology', 'status' => 1]);
        $doctor->specializations()->sync([$spec->id]);
        $doctor->specializations()->sync([]);

        $this->assertCount(0, $doctor->fresh()->specializations);
    }
}
