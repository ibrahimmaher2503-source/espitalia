<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_only_the_calling_token(): void
    {
        $user = User::factory()->create(['player_id' => 'device-token']);
        $currentToken = $user->createToken('patient-app');
        $current = $currentToken->plainTextToken;
        $other = $user->createToken('another-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$current)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);

        Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$current)
            ->getJson('/api/user')
            ->assertUnauthorized();
        Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$other)
            ->getJson('/api/user')
            ->assertOk();
        $this->assertNull($user->fresh()->player_id);
    }
}
