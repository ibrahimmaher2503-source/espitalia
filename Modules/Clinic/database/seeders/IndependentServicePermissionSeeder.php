<?php

namespace Modules\Clinic\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IndependentServicePermissionSeeder extends Seeder
{
    public function run()
    {
        // Get existing roles
        $admin = Role::where('name', 'admin')->first();
        $demoAdmin = Role::where('name', 'demo_admin')->first();
        $doctor = Role::where('name', 'doctor')->first();

        // Admin permissions for Independent Services
        $independentServicePermissions = [
            'view_doctor_independent_services',
            'add_doctor_independent_services',
            'edit_doctor_independent_services',
            'delete_doctor_independent_services',
        ];

        // Admin permissions for Independent Sessions
        $independentSessionPermissions = [
            'view_doctor_independent_sessions',
            'add_doctor_independent_sessions',
            'edit_doctor_independent_sessions',
            'delete_doctor_independent_sessions',
        ];

        // Admin permissions for Call Services
        $callServicePermissions = [
            'view_doctor_call_services',
            'add_doctor_call_services',
            'edit_doctor_call_services',
            'delete_doctor_call_services',
        ];

        // Admin permissions for Call Sessions
        $callSessionPermissions = [
            'view_doctor_call_sessions',
            'add_doctor_call_sessions',
            'edit_doctor_call_sessions',
            'delete_doctor_call_sessions',
        ];

        // Doctor-specific permissions (self-management)
        $doctorOwnPermissions = [
            'manage_own_independent_services',
            'manage_own_independent_sessions',
            'manage_own_call_services',
            'manage_own_call_sessions',
        ];

        // Create all permissions
        $allPermissions = array_merge(
            $independentServicePermissions,
            $independentSessionPermissions,
            $callServicePermissions,
            $callSessionPermissions,
            $doctorOwnPermissions
        );

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'is_fixed' => true]);
        }

        // Assign ALL admin permissions to admin & demo_admin
        $adminPermissions = array_merge(
            $independentServicePermissions,
            $independentSessionPermissions,
            $callServicePermissions,
            $callSessionPermissions
        );

        if ($admin) {
            $admin->givePermissionTo($adminPermissions);
        }
        if ($demoAdmin) {
            $demoAdmin->givePermissionTo($adminPermissions);
        }

        // Assign doctor self-management permissions
        if ($doctor) {
            $doctor->givePermissionTo($doctorOwnPermissions);
        }
    }
}
