<?php

namespace Modules\IntensiveCare\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class IntensiveCareCommissionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_icu_commission',
            'add_icu_commission',
            'edit_icu_commission',
            'delete_icu_commission',
            'view_icu_earning',
            'edit_icu_earning',
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

        // Assign view_icu_earning to hospital_coordinator role
        $coordinatorRole = Role::where('name', 'hospital_coordinator')->first();
        if ($coordinatorRole) {
            $coordinatorRole->givePermissionTo('view_icu_earning');
        }
    }
}
