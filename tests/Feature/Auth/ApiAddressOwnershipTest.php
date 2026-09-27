<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAddressOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_read_or_mutate_another_patients_address(): void
    {
        $owner = User::factory()->create(['user_type' => 'user']);
        $otherPatient = User::factory()->create(['user_type' => 'user']);
        $address = $owner->addresses()->create($this->addressPayload());

        $this->actingAs($otherPatient, 'sanctum')
            ->getJson('/api/address-list?user_id='.$owner->id)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($otherPatient, 'sanctum')
            ->getJson('/api/remove-address?id='.$address->id)
            ->assertNotFound();

        $this->actingAs($otherPatient, 'sanctum')
            ->postJson('/api/edit-address', array_merge(
                ['id' => $address->id],
                $this->addressPayload(['address_line_1' => 'attacker edit'])
            ))
            ->assertNotFound();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address_line_1' => '10 Patient Street',
        ]);
    }

    public function test_store_ignores_a_client_supplied_user_id_and_scopes_to_the_token(): void
    {
        $patient = User::factory()->create(['user_type' => 'user']);
        $otherPatient = User::factory()->create(['user_type' => 'user']);

        $this->actingAs($patient, 'sanctum')
            ->postJson('/api/add-address', array_merge(
                ['user_id' => $otherPatient->id],
                $this->addressPayload()
            ))
            ->assertOk();

        $this->assertDatabaseHas('addresses', [
            'addressable_id' => $patient->id,
            'addressable_type' => User::class,
            'address_line_1' => '10 Patient Street',
        ]);
        $this->assertDatabaseMissing('addresses', [
            'addressable_id' => $otherPatient->id,
            'address_line_1' => '10 Patient Street',
        ]);
    }

    /** @return array<string, mixed> */
    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'address_line_1' => '10 Patient Street',
            'address_line_2' => 'Apartment 2',
            'postal_code' => '12345',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'country' => 'Egypt',
            'is_primary' => false,
        ], $overrides);
    }
}
