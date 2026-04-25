<?php

namespace Modules\Specialization\Tests\Feature;

use App\Models\User;
use Modules\RequestNurse\Models\Nurse;
use Modules\Specialization\Models\Specialization;

class MigrateNurseSpecializationsTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runNurseMigrations();
    }

    protected function runNurseMigrations(): void
    {
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
        $this->createMinimalNursesTable();
    }

    protected function createNurse(?string $specialization = null): Nurse
    {
        static $counter = 0;
        $counter++;

        $user = User::create([
            'first_name' => 'Nurse',
            'last_name' => "Test{$counter}",
            'email' => "nurse{$counter}@test.com",
            'password' => bcrypt('password'),
            'user_type' => 'doctor',
        ]);

        $data = [
            'nurse_id' => $user->id,
            'hourly_rate' => 50,
            'availability_status' => 'available',
        ];

        if ($specialization !== null) {
            $data['specialization_en'] = $specialization;
        }

        return Nurse::create($data);
    }

    public function test_exact_match_creates_pivot_record(): void
    {
        $spec = Specialization::create(['name_en' => 'ICU Nursing', 'status' => 1]);
        $nurse = $this->createNurse('ICU Nursing');

        $this->artisan('specialization:migrate-nurses')
            ->assertExitCode(0);

        $this->assertTrue($nurse->fresh()->specializations->contains($spec));
    }

    public function test_case_insensitive_match_works(): void
    {
        $spec = Specialization::create(['name_en' => 'Pediatric Nursing', 'status' => 1]);
        $nurse = $this->createNurse('pediatric nursing');

        $this->artisan('specialization:migrate-nurses')
            ->assertExitCode(0);

        $this->assertTrue($nurse->fresh()->specializations->contains($spec));
    }

    public function test_no_match_does_not_create_pivot(): void
    {
        Specialization::create(['name_en' => 'ICU Nursing', 'status' => 1]);
        $nurse = $this->createNurse('Unknown Specialty');

        $this->artisan('specialization:migrate-nurses')
            ->assertExitCode(0);

        $this->assertCount(0, $nurse->fresh()->specializations);
    }

    public function test_already_migrated_nurse_not_duplicated(): void
    {
        $spec = Specialization::create(['name_en' => 'ICU Nursing', 'status' => 1]);
        $nurse = $this->createNurse('ICU Nursing');

        $this->artisan('specialization:migrate-nurses');
        $this->artisan('specialization:migrate-nurses');

        $this->assertCount(1, $nurse->fresh()->specializations);
    }
}
