<?php

namespace Modules\IntensiveCare\database\seeders;

use Illuminate\Database\Seeder;
use Modules\IntensiveCare\Database\Seeders\IntensiveCareCommissionPermissionSeeder;

class IntensiveCareDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            HospitalSeeder::class,
            IcuDepartmentSeeder::class,
            IntensiveCareCommissionPermissionSeeder::class,
        ]);
    }
}
