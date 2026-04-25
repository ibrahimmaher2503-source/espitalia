<?php

namespace Modules\DoctorVisit\database\seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DoctorVisitDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permissions ───────────────────────────────────────────
        $permissions = [
            'view_doctor_visit',
            'create_doctor_visit',
            'update_status_doctor_visit',
            'assign_doctor_visit',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'is_fixed' => true]);
        }

        // ── Role assignments ──────────────────────────────────────
        $admin        = Role::where('name', 'admin')->first();
        $demoAdmin    = Role::where('name', 'demo_admin')->first();
        $receptionist = Role::where('name', 'receptionist')->first();
        $doctor       = Role::where('name', 'doctor')->first();
        $user         = Role::where('name', 'user')->first();

        // Admin + receptionist: all permissions
        foreach ([$admin, $receptionist] as $role) {
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        // Doctor: view + update status (not assign)
        if ($doctor) {
            $doctor->givePermissionTo(['view_doctor_visit', 'update_status_doctor_visit']);
        }

        // Patient (user role): view own + create
        if ($user) {
            $user->givePermissionTo(['view_doctor_visit', 'create_doctor_visit']);
        }

        // Demo admin: view only
        if ($demoAdmin) {
            $demoAdmin->givePermissionTo(['view_doctor_visit']);
        }
    }
}
