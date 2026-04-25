<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RadiologyLabPermissionSeeder extends Seeder
{
    public function run()
    {
        // Create Lab Technician role
        $labTechnician = Role::firstOrCreate([
            'name' => 'lab_technician',
            'title' => 'Lab Technician',
            'is_fixed' => true,
        ]);

        // Get existing roles
        $admin = Role::where('name', 'admin')->first();
        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $doctor = Role::where('name', 'doctor')->first();

        // Lab Test Categories permissions
        $categoryPermissions = [
            'view_lab_test_categories',
            'add_lab_test_categories',
            'edit_lab_test_categories',
            'delete_lab_test_categories',
        ];

        // Lab Tests permissions
        $testPermissions = [
            'view_lab_tests',
            'add_lab_tests',
            'edit_lab_tests',
            'delete_lab_tests',
        ];

        // Test Orders permissions
        $orderPermissions = [
            'view_test_orders',
            'add_test_orders',
            'edit_test_orders',
            'delete_test_orders',
            'test_orders_assign',
        ];

        // Test Results permission
        $resultPermissions = [
            'manage_test_results',
        ];

        // Labs permissions
        $labPermissions = [
            'view_labs',
            'add_labs',
            'edit_labs',
            'delete_labs',
        ];

        // Radiology Centers permissions
        $radiologyCenterPermissions = [
            'view_radiology_centers',
            'add_radiology_centers',
            'edit_radiology_centers',
            'delete_radiology_centers',
        ];

        // Facility Session permissions
        $sessionPermissions = [
            'view_facility_sessions',
            'add_facility_sessions',
            'edit_facility_sessions',
            'delete_facility_sessions',
        ];

        // Facility Booking permissions
        $bookingPermissions = [
            'view_facility_bookings',
            'manage_facility_bookings',
        ];

        // Create all permissions
        $allPermissions = array_merge($categoryPermissions, $testPermissions, $orderPermissions, $resultPermissions, $labPermissions, $radiologyCenterPermissions, $sessionPermissions, $bookingPermissions);
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

        // Lab Technician: view tests, view orders, manage results
        $labTechnician->givePermissionTo([
            'view_lab_tests',
            'view_test_orders',
            'manage_test_results',
        ]);

        // Doctor: view tests, view orders, add orders
        if ($doctor) {
            $doctor->givePermissionTo([
                'view_lab_tests',
                'view_test_orders',
                'add_test_orders',
            ]);
        }
    }
}
