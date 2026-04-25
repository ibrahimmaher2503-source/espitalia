<?php

namespace Modules\Offer\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OfferPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // CRUD permissions
        $crudModules = ['offers'];
        $adminPermissions = [];

        foreach ($crudModules as $module) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $action.'_'.$module],
                    ['guard_name' => 'web']
                );
                $adminPermissions[] = $permission->id;
            }
        }

        // Specialized permissions
        $specialPermissions = [
            'change_status_offers',
            'bulk_action_offers',
            'view_offer_analytics',
            'manage_offer_coupons',
        ];

        foreach ($specialPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
            $adminPermissions[] = $permission->id;
        }

        // Assign all to admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($adminPermissions);
        }

        // demo_admin gets all except delete and bulk_action
        $demoAdminRole = Role::where('name', 'demo_admin')->first();
        if ($demoAdminRole) {
            $demoPermissions = Permission::whereIn('name', [
                'view_offers', 'add_offers', 'edit_offers',
                'change_status_offers', 'view_offer_analytics', 'manage_offer_coupons',
            ])->pluck('id')->toArray();
            $demoAdminRole->givePermissionTo($demoPermissions);
        }

        // Vendor gets own-scoped permissions
        $vendorRole = Role::where('name', 'vendor')->first();
        if ($vendorRole) {
            $vendorPermissions = Permission::whereIn('name', [
                'view_offers', 'add_offers', 'edit_offers',
                'change_status_offers', 'view_offer_analytics', 'manage_offer_coupons',
            ])->pluck('id')->toArray();
            $vendorRole->givePermissionTo($vendorPermissions);
        }

        // Receptionist can manage coupons (apply during booking)
        $receptionistRole = Role::where('name', 'receptionist')->first();
        if ($receptionistRole) {
            $receptionistPermissions = Permission::whereIn('name', [
                'manage_offer_coupons',
            ])->pluck('id')->toArray();
            $receptionistRole->givePermissionTo($receptionistPermissions);
        }
    }
}
