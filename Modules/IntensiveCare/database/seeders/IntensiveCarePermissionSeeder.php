<?php

namespace Modules\IntensiveCare\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IntensiveCarePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Hospital Coordinator role
        $hospitalCoordinator = Role::firstOrCreate([
            'name' => 'hospital_coordinator',
            'title' => 'Hospital Coordinator',
            'is_fixed' => true,
        ]);

        // Get existing roles
        $admin = Role::where('name', 'admin')->first();
        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $doctor = Role::where('name', 'doctor')->first();

        // Hospital permissions
        $hospitalPermissions = [
            'view_hospitals',
            'add_hospitals',
            'edit_hospitals',
            'delete_hospitals',
        ];

        // ICU Departments permissions
        $icuDepartmentPermissions = [
            'view_icu_departments',
            'add_icu_departments',
            'edit_icu_departments',
            'delete_icu_departments',
        ];

        // Admission Requests permissions
        $admissionRequestPermissions = [
            'view_icu_admission_requests',
            'manage_icu_admission_requests',
            'edit_icu_admission_requests',
            'delete_icu_admission_requests',
        ];

        // Create all permissions
        $allPermissions = array_merge($hospitalPermissions, $icuDepartmentPermissions, $admissionRequestPermissions);
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'is_fixed' => true]);
        }

        // Assign ALL permissions to admin & demo_admin
        if ($admin) {
            $admin->givePermissionTo($allPermissions);
        }
        if ($demoAdmin) {
            $demoAdmin->givePermissionTo($allPermissions);
        }

        // Hospital Coordinator: view/edit hospitals, view/add/edit icu departments, view/manage admission requests
        $hospitalCoordinator->givePermissionTo([
            'view_hospitals',
            'edit_hospitals',
            'view_icu_departments',
            'add_icu_departments',
            'edit_icu_departments',
            'view_icu_admission_requests',
            'manage_icu_admission_requests',
        ]);

        // Doctor: view hospitals, view icu departments
        if ($doctor) {
            $doctor->givePermissionTo([
                'view_hospitals',
                'view_icu_departments',
            ]);
        }
    }
}
