<?php

namespace Tests\Feature;

use App\Models\User;
use Modules\DoctorVisit\Tests\Unit\BaseTest;
use Spatie\Permission\Models\Role;

class RoleGuardProbeTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
    }

    public function test_role_assignment_is_visible_to_the_api_authorization_guard(): void
    {
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'], ['title' => 'User']);
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->load('roles');

        $this->assertSame(['user'], $user->roles->pluck('name')->all());
        $this->assertTrue($user->hasRole('user'));
    }
}
