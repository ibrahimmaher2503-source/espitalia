<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_login_requires_a_signed_identity_token(): void
    {
        $response = $this->postJson('/api/social-login', [
            'login_type' => 'google',
            'email' => 'attacker@example.com',
            'user_type' => 'admin',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_forged_social_token_cannot_create_a_user_or_escalate_role(): void
    {
        $response = $this->postJson('/api/social-login', [
            'login_type' => 'google',
            'id_token' => 'not-a-signed-firebase-token',
            'email' => 'attacker@example.com',
            'user_type' => 'admin',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_patient_login_issues_a_token_without_otp(): void
    {
        $user = User::factory()->create([
            'user_type' => 'user',
            'password' => Hash::make('patient-password'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'patient-password',
            'user_type' => 'user',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['api_token']]);

        $this->postJson('/api/otp-verify', [
            'email' => $user->email,
            'otp' => '000000',
        ])->assertNotFound();
    }

    public function test_change_password_ignores_a_client_supplied_user_id_and_rejects_wrong_current_password(): void
    {
        $owner = User::factory()->create([
            'user_type' => 'user',
            'password' => Hash::make('owner-password'),
        ]);
        $attacker = User::factory()->create([
            'user_type' => 'user',
            'password' => Hash::make('attacker-password'),
        ]);

        $this->actingAs($attacker, 'sanctum')
            ->postJson('/api/change-password', [
                'id' => $owner->id,
                'old_password' => 'owner-password',
                'new_password' => 'changed-password',
            ])
            ->assertUnprocessable();

        $this->assertTrue(Hash::check('owner-password', $owner->fresh()->password));
        $this->assertTrue(Hash::check('attacker-password', $attacker->fresh()->password));
    }

    public function test_profile_and_user_detail_are_scoped_to_the_bearer_user(): void
    {
        $owner = User::factory()->create(['user_type' => 'user', 'first_name' => 'Owner']);
        $otherPatient = User::factory()->create(['user_type' => 'user', 'first_name' => 'Patient']);

        $this->actingAs($otherPatient, 'sanctum')
            ->postJson('/api/update-profile', [
                'id' => $owner->id,
                'first_name' => 'Updated Patient',
                'user_type' => 'admin',
                'is_banned' => 1,
            ])
            ->assertOk();

        $this->assertSame('Owner', $owner->fresh()->first_name);
        $this->assertSame('Updated Patient', $otherPatient->fresh()->first_name);
        $this->assertSame('user', $otherPatient->fresh()->user_type);
        $this->assertSame(0, (int) $otherPatient->fresh()->is_banned);

        $this->app['auth']->forgetGuards();
        $this->getJson('/api/user-detail?id='.$owner->id)->assertUnauthorized();
        $this->actingAs($otherPatient, 'sanctum')
            ->getJson('/api/user-detail?id='.$owner->id)
            ->assertOk()
            ->assertJsonPath('data.id', $otherPatient->id);
    }

    public function test_authenticated_profile_update_accepts_fcm_player_id(): void
    {
        $patient = User::factory()->create(['user_type' => 'user', 'player_id' => null]);

        $this->actingAs($patient, 'sanctum')
            ->postJson('/api/update-profile', ['player_id' => 'qa-fcm-token'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertSame('qa-fcm-token', $patient->fresh()->player_id);
    }
}
