<?php

namespace Modules\Specialization\Tests\Feature;

use App\Models\User;
use Modules\RequestNurse\Models\Nurse;
use Modules\Specialization\Models\Specialization;

class NurseSpecializationTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
        $this->createMinimalNursesTable();
    }

    protected function createNurseWithUser(): Nurse
    {
        $user = User::create([
            'first_name' => 'Nurse',
            'last_name' => 'Test',
            'email' => 'nurse'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'doctor',
        ]);

        return Nurse::create([
            'nurse_id' => $user->id,
            'hourly_rate' => 50,
            'availability_status' => 'available',
        ]);
    }

    public function test_nurse_has_specializations_relationship(): void
    {
        $nurse = $this->createNurseWithUser();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $nurse->specializations());
    }

    public function test_sync_specializations_on_nurse(): void
    {
        $nurse = $this->createNurseWithUser();

        $spec1 = Specialization::create(['name_en' => 'ICU Nursing', 'status' => 1]);
        $spec2 = Specialization::create(['name_en' => 'Pediatric Nursing', 'status' => 1]);

        $nurse->specializations()->sync([$spec1->id, $spec2->id]);

        $this->assertCount(2, $nurse->fresh()->specializations);
    }

    public function test_replace_specializations(): void
    {
        $nurse = $this->createNurseWithUser();

        $spec1 = Specialization::create(['name_en' => 'ICU', 'status' => 1]);
        $spec2 = Specialization::create(['name_en' => 'Pediatric', 'status' => 1]);

        $nurse->specializations()->sync([$spec1->id]);
        $nurse->specializations()->sync([$spec2->id]);

        $fresh = $nurse->fresh()->specializations;
        $this->assertCount(1, $fresh);
        $this->assertTrue($fresh->contains($spec2));
    }
}
