<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Modules\IntensiveCare\Database\Seeders\IntensiveCareCommissionPermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IcuCommissionPermissionSeederTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create prerequisite roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['title' => 'Admin']);
        Role::firstOrCreate(['name' => 'hospital_coordinator', 'guard_name' => 'web'], ['title' => 'Hospital Coordinator']);
    }

    public function test_seeder_creates_all_six_permissions()
    {
        $this->seed(IntensiveCareCommissionPermissionSeeder::class);

        $expectedPermissions = [
            'view_icu_commission',
            'add_icu_commission',
            'edit_icu_commission',
            'delete_icu_commission',
            'view_icu_earning',
            'edit_icu_earning',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission, "Permission '{$permissionName}' should exist");
        }
    }

    public function test_all_permissions_assigned_to_admin_role()
    {
        $this->seed(IntensiveCareCommissionPermissionSeeder::class);

        $admin = Role::where('name', 'admin')->first();

        $expectedPermissions = [
            'view_icu_commission',
            'add_icu_commission',
            'edit_icu_commission',
            'delete_icu_commission',
            'view_icu_earning',
            'edit_icu_earning',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $this->assertTrue(
                $admin->hasPermissionTo($permissionName),
                "Admin should have permission '{$permissionName}'"
            );
        }
    }

    public function test_view_icu_earning_assigned_to_hospital_coordinator()
    {
        $this->seed(IntensiveCareCommissionPermissionSeeder::class);

        $coordinator = Role::where('name', 'hospital_coordinator')->first();

        $this->assertTrue(
            $coordinator->hasPermissionTo('view_icu_earning'),
            'Hospital coordinator should have view_icu_earning permission'
        );
    }

    public function test_seeder_is_idempotent()
    {
        $this->seed(IntensiveCareCommissionPermissionSeeder::class);

        $initialPermissionCount = Permission::count();

        // Run seeder again
        $this->seed(IntensiveCareCommissionPermissionSeeder::class);

        $finalPermissionCount = Permission::count();

        $this->assertEquals($initialPermissionCount, $finalPermissionCount);
    }
}
