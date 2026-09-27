<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Models\OtherPatient;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiPatientDataOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_read_their_own_encounter_list(): void
    {
        Role::query()->firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
            ['title' => 'User'],
        );
        $patient = User::factory()->create(['user_type' => 'user']);
        $patient->assignRole('user');

        $this->actingAs($patient, 'sanctum')
            ->getJson('/api/encounter-list')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_family_member_api_ignores_foreign_owner_and_blocks_foreign_mutation(): void
    {
        $patient = User::factory()->create(['user_type' => 'user']);
        $otherPatient = User::factory()->create(['user_type' => 'user']);
        $foreignMember = OtherPatient::create([
            'user_id' => $otherPatient->id,
            'first_name' => 'Foreign',
            'last_name' => 'Member',
            'dob' => '1990-01-01',
            'gender' => 'female',
            'relation' => 'sibling',
            'contactNumber' => '+201000000000',
        ]);

        $this->actingAs($patient, 'sanctum')
            ->getJson('/api/other-members-list?patient_id='.$otherPatient->id)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($patient, 'sanctum')
            ->postJson('/api/delete-other_member/'.$foreignMember->id)
            ->assertNotFound();

        $this->assertDatabaseHas('other_patients', [
            'id' => $foreignMember->id,
            'user_id' => $otherPatient->id,
        ]);
    }

    public function test_family_member_create_ignores_a_client_supplied_owner(): void
    {
        $patient = User::factory()->create(['user_type' => 'user']);
        $otherPatient = User::factory()->create(['user_type' => 'user']);

        $this->actingAs($patient, 'sanctum')
            ->postJson('/api/add-patient-member', [
                'user_id' => $otherPatient->id,
                'first_name' => 'Owned',
                'last_name' => 'Member',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'relation' => 'sibling',
                'contactNumber' => '+201000000000',
            ])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('other_patients', [
            'user_id' => $patient->id,
            'first_name' => 'Owned',
        ]);
        $this->assertDatabaseMissing('other_patients', [
            'user_id' => $otherPatient->id,
            'first_name' => 'Owned',
        ]);
    }
}
