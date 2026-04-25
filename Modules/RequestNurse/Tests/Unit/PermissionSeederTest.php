<?php

namespace Modules\RequestNurse\Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use Modules\RequestNurse\Database\Seeders\RequestNursePermissionSeeder;

class PermissionSeederTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();

        // Create prerequisite roles
        Role::firstOrCreate(['name' => 'admin', 'title' => 'Admin']);
        Role::firstOrCreate(['name' => 'demo_admin', 'title' => 'Demo Admin']);
    }

    public function test_seeder_creates_nurse_role()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $nurseRole = Role::where('name', 'nurse')->first();
        $this->assertNotNull($nurseRole);
        $this->assertEquals('Nurse', $nurseRole->title);
        $this->assertNotEmpty($nurseRole->is_fixed);
    }

    public function test_seeder_creates_all_request_nurse_permissions()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $expectedPermissions = [
            'view_request_nurse',
            'add_request_nurse',
            'edit_request_nurse',
            'delete_request_nurse',
            'request_nurse_assign',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission);
            $this->assertNotEmpty($permission->is_fixed);
        }
    }

    public function test_seeder_creates_all_nurses_management_permissions()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $expectedPermissions = [
            'view_nurses',
            'add_nurses',
            'edit_nurses',
            'delete_nurses',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission);
            $this->assertNotEmpty($permission->is_fixed);
        }
    }

    public function test_admin_role_has_all_permissions()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $admin = Role::where('name', 'admin')->first();
        $allPermissions = array_merge(
            ['view_request_nurse', 'add_request_nurse', 'edit_request_nurse', 'delete_request_nurse', 'request_nurse_assign'],
            ['view_nurses', 'add_nurses', 'edit_nurses', 'delete_nurses']
        );

        foreach ($allPermissions as $permissionName) {
            $this->assertTrue($admin->hasPermissionTo($permissionName));
        }
    }

    public function test_demo_admin_role_has_all_permissions()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $allPermissions = array_merge(
            ['view_request_nurse', 'add_request_nurse', 'edit_request_nurse', 'delete_request_nurse', 'request_nurse_assign'],
            ['view_nurses', 'add_nurses', 'edit_nurses', 'delete_nurses']
        );

        foreach ($allPermissions as $permissionName) {
            $this->assertTrue($demoAdmin->hasPermissionTo($permissionName));
        }
    }

    public function test_nurse_role_has_only_view_permission()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $nurse = Role::where('name', 'nurse')->first();

        // Nurse should have view_request_nurse permission
        $this->assertTrue($nurse->hasPermissionTo('view_request_nurse'));

        // Nurse should NOT have other permissions
        $otherPermissions = [
            'add_request_nurse',
            'edit_request_nurse',
            'delete_request_nurse',
            'request_nurse_assign',
            'view_nurses',
            'add_nurses',
            'edit_nurses',
            'delete_nurses',
        ];

        foreach ($otherPermissions as $permissionName) {
            $this->assertFalse($nurse->hasPermissionTo($permissionName));
        }
    }

    public function test_seeder_is_idempotent()
    {
        $this->seed(RequestNursePermissionSeeder::class);

        $initialRoleCount = Role::count();
        $initialPermissionCount = Permission::count();

        // Run seeder again
        $this->seed(RequestNursePermissionSeeder::class);

        $finalRoleCount = Role::count();
        $finalPermissionCount = Permission::count();

        // Counts should be the same (no duplicates created)
        $this->assertEquals($initialRoleCount, $finalRoleCount);
        $this->assertEquals($initialPermissionCount, $finalPermissionCount);
    }
}
