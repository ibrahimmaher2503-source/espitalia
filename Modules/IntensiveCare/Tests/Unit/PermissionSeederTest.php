<?php

namespace Modules\IntensiveCare\Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use Modules\IntensiveCare\database\seeders\IntensiveCarePermissionSeeder;

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

    public function test_seeder_creates_hospital_coordinator_role()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $hospitalCoordinator = Role::where('name', 'hospital_coordinator')->first();
        $this->assertNotNull($hospitalCoordinator);
        $this->assertEquals('Hospital Coordinator', $hospitalCoordinator->title);
        $this->assertNotEmpty($hospitalCoordinator->is_fixed);
    }

    public function test_seeder_creates_all_hospital_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $expectedPermissions = [
            'view_hospitals',
            'add_hospitals',
            'edit_hospitals',
            'delete_hospitals',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission);
            $this->assertNotEmpty($permission->is_fixed);
        }
    }

    public function test_seeder_creates_all_icu_department_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $expectedPermissions = [
            'view_icu_departments',
            'add_icu_departments',
            'edit_icu_departments',
            'delete_icu_departments',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission);
            $this->assertNotEmpty($permission->is_fixed);
        }
    }

    public function test_seeder_creates_all_admission_request_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $expectedPermissions = [
            'view_icu_admission_requests',
            'manage_icu_admission_requests',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            $this->assertNotNull($permission);
            $this->assertNotEmpty($permission->is_fixed);
        }
    }

    public function test_admin_role_has_all_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $admin = Role::where('name', 'admin')->first();
        $allPermissions = array_merge(
            ['view_hospitals', 'add_hospitals', 'edit_hospitals', 'delete_hospitals'],
            ['view_icu_departments', 'add_icu_departments', 'edit_icu_departments', 'delete_icu_departments'],
            ['view_icu_admission_requests', 'manage_icu_admission_requests']
        );

        foreach ($allPermissions as $permissionName) {
            $this->assertTrue($admin->hasPermissionTo($permissionName));
        }
    }

    public function test_demo_admin_role_has_all_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $allPermissions = array_merge(
            ['view_hospitals', 'add_hospitals', 'edit_hospitals', 'delete_hospitals'],
            ['view_icu_departments', 'add_icu_departments', 'edit_icu_departments', 'delete_icu_departments'],
            ['view_icu_admission_requests', 'manage_icu_admission_requests']
        );

        foreach ($allPermissions as $permissionName) {
            $this->assertTrue($demoAdmin->hasPermissionTo($permissionName));
        }
    }

    public function test_hospital_coordinator_has_correct_permissions()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $hospitalCoordinator = Role::where('name', 'hospital_coordinator')->first();

        // Hospital Coordinator should have these permissions
        $expectedPermissions = [
            'view_hospitals',
            'edit_hospitals',
            'view_icu_departments',
            'add_icu_departments',
            'edit_icu_departments',
            'view_icu_admission_requests',
            'manage_icu_admission_requests',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $this->assertTrue($hospitalCoordinator->hasPermissionTo($permissionName));
        }

        // Hospital Coordinator should NOT have these permissions
        $notExpectedPermissions = [
            'add_hospitals',
            'delete_hospitals',
            'delete_icu_departments',
        ];

        foreach ($notExpectedPermissions as $permissionName) {
            $this->assertFalse($hospitalCoordinator->hasPermissionTo($permissionName));
        }
    }

    public function test_doctor_has_view_only_permissions()
    {
        Role::firstOrCreate(['name' => 'doctor', 'title' => 'Doctor']);

        $this->seed(IntensiveCarePermissionSeeder::class);

        $doctor = Role::where('name', 'doctor')->first();

        // Doctor should have view permissions
        $this->assertTrue($doctor->hasPermissionTo('view_hospitals'));
        $this->assertTrue($doctor->hasPermissionTo('view_icu_departments'));

        // Doctor should NOT have other permissions
        $otherPermissions = [
            'add_hospitals',
            'edit_hospitals',
            'delete_hospitals',
            'add_icu_departments',
            'edit_icu_departments',
            'delete_icu_departments',
            'view_icu_admission_requests',
            'manage_icu_admission_requests',
        ];

        foreach ($otherPermissions as $permissionName) {
            $this->assertFalse($doctor->hasPermissionTo($permissionName));
        }
    }

    public function test_seeder_is_idempotent()
    {
        $this->seed(IntensiveCarePermissionSeeder::class);

        $initialRoleCount = Role::count();
        $initialPermissionCount = Permission::count();

        // Run seeder again
        $this->seed(IntensiveCarePermissionSeeder::class);

        $finalRoleCount = Role::count();
        $finalPermissionCount = Permission::count();

        // Counts should be the same (no duplicates created)
        $this->assertEquals($initialRoleCount, $finalRoleCount);
        $this->assertEquals($initialPermissionCount, $finalPermissionCount);
    }
}
