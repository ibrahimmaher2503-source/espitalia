<?php

namespace Modules\Pharmacy\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PharmacyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create pharmacy role
        $pharmacyRole = Role::firstOrCreate(
            ['name' => 'pharmacy'],
            ['title' => 'Pharmacy', 'is_fixed' => true, 'guard_name' => 'web']
        );

        // Define permission modules
        $modules = [
            'pharmacy_categories',
            'pharmacy_brands',
            'pharmacy_product_types',
            'pharmacy_products',
            'pharmacies',
            'pharmacy_inventory',
            'pharmacy_orders',
        ];

        // Create permissions for each module
        $adminPermissions = [];
        $pharmacyPermissions = [];

        foreach ($modules as $module) {
            $actions = ['view', 'add', 'edit', 'delete'];

            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $action.'_'.$module],
                    ['guard_name' => 'web']
                );
                $adminPermissions[] = $permission->id;
            }
        }

        // New module permissions (CRUD style)
        $newModules = [
            'pharmacy_staff',
            'pharmacy_batches',
            'pharmacy_delivery_zones',
            'pharmacy_coupons',
        ];

        foreach ($newModules as $module) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => $action.'_'.$module],
                    ['guard_name' => 'web']
                );
                $adminPermissions[] = $permission->id;
            }
        }

        // Specialized permissions (not standard CRUD)
        $specialPermissions = [
            'view_pharmacy_prescriptions',
            'review_pharmacy_prescriptions',
            'view_pharmacy_refunds',
            'process_pharmacy_refunds',
            'view_pharmacy_reports',
            'view_pharmacy_notifications',
            'view_pharmacy_audit',
        ];

        foreach ($specialPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
            $adminPermissions[] = $permission->id;
        }

        // Pharmacy staff can manage inventory and view/edit their orders
        $pharmacyInventoryPermissions = [
            'view_pharmacy_inventory',
            'add_pharmacy_inventory',
            'edit_pharmacy_inventory',
            'delete_pharmacy_inventory',
        ];

        $pharmacyOrderPermissions = [
            'view_pharmacy_orders',
            'edit_pharmacy_orders',
        ];

        // New pharmacy panel permissions
        $pharmacyPanelPermissions = [
            // Staff
            'view_pharmacy_staff',
            'add_pharmacy_staff',
            'edit_pharmacy_staff',
            'delete_pharmacy_staff',
            // Batches
            'view_pharmacy_batches',
            'add_pharmacy_batches',
            'edit_pharmacy_batches',
            // Prescriptions
            'view_pharmacy_prescriptions',
            'review_pharmacy_prescriptions',
            // Delivery Zones
            'view_pharmacy_delivery_zones',
            'add_pharmacy_delivery_zones',
            'edit_pharmacy_delivery_zones',
            'delete_pharmacy_delivery_zones',
            // Coupons
            'view_pharmacy_coupons',
            'add_pharmacy_coupons',
            'edit_pharmacy_coupons',
            'delete_pharmacy_coupons',
            // Refunds
            'view_pharmacy_refunds',
            // Reports
            'view_pharmacy_reports',
            // Notifications
            'view_pharmacy_notifications',
        ];

        foreach (array_merge($pharmacyInventoryPermissions, $pharmacyOrderPermissions, $pharmacyPanelPermissions) as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
            $pharmacyPermissions[] = $permission->id;
        }

        // Assign all permissions to admin role (givePermissionTo preserves existing permissions)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($adminPermissions);
        }

        // Assign pharmacy-specific permissions to pharmacy role
        $pharmacyRole->givePermissionTo($pharmacyPermissions);
    }
}
