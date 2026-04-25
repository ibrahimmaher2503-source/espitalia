<?php

namespace Modules\Branch\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BranchPermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            'view_branch',
            'add_branch',
            'edit_branch',
            'delete_branch',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => true]);
        }

        // Assign to roles
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        $demoAdmin = Role::where('name', 'demo_admin')->first();
        if ($demoAdmin) {
            $demoAdmin->givePermissionTo($permissions);
        }

        $vendor = Role::where('name', 'vendor')->first();
        if ($vendor) {
            $vendor->givePermissionTo($permissions);
        }
    }
}
