<?php

namespace Modules\Pharmacy\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PharmacyCommissionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_pharmacy_commission',
            'add_pharmacy_commission',
            'edit_pharmacy_commission',
            'delete_pharmacy_commission',
            'view_pharmacy_earning',
            'edit_pharmacy_earning',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
