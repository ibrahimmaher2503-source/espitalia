<?php

namespace Modules\RequestNurse\database\seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RequestNursePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ── Create the Nurse role ───────────────────────────────
        $nurse = Role::firstOrCreate([
            'name' => 'nurse',
            'title' => 'Nurse',
            'is_fixed' => true,
        ]);

        // ── Ensure existing roles are loaded ────────────────────
        $admin = Role::where('name', 'admin')->first();
        $demoAdmin = Role::where('name', 'demo_admin')->first();

        // ── Create Request Nurse permissions ────────────────────
        $requestNursePermissions = [
            'view_request_nurse',
            'add_request_nurse',
            'edit_request_nurse',
            'delete_request_nurse',
            'request_nurse_assign',
        ];

        foreach ($requestNursePermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'is_fixed' => true]);
        }

        // ── Create Nurses management permissions ────────────────
        $nursesPermissions = [
            'view_nurses',
            'add_nurses',
            'edit_nurses',
            'delete_nurses',
        ];

        foreach ($nursesPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'is_fixed' => true]);
        }

        // ── Assign ALL permissions to admin ─────────────────────
        $allPermissions = array_merge($requestNursePermissions, $nursesPermissions);
        if ($admin) {
            $admin->givePermissionTo($allPermissions);
        }
        if ($demoAdmin) {
            $demoAdmin->givePermissionTo($allPermissions);
        }

        // ── Assign limited permissions to nurse role ────────────
        // Nurses can only view their assigned requests
        $nurse->givePermissionTo([
            'view_request_nurse',
        ]);
    }
}
