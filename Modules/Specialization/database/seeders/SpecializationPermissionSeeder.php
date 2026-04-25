<?php

namespace Modules\Specialization\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpecializationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminPermissions = [];

        foreach (['view', 'add', 'edit', 'delete'] as $action) {
            $permission = Permission::firstOrCreate(
                ['name' => $action.'_specialization'],
                ['guard_name' => 'web']
            );
            $adminPermissions[] = $permission->id;
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($adminPermissions);
        }

        $demoAdminRole = Role::where('name', 'demo_admin')->first();
        if ($demoAdminRole) {
            $demoPermissions = Permission::whereIn('name', [
                'view_specialization', 'add_specialization', 'edit_specialization',
            ])->pluck('id')->toArray();
            $demoAdminRole->givePermissionTo($demoPermissions);
        }
    }
}
