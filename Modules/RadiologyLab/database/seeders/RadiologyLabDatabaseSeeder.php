<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;

class RadiologyLabDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RadiologyLabPermissionSeeder::class,
            LabTestCategorySeeder::class,
            LabTestSeeder::class,
            LabSeeder::class,
            RadiologyCenterSeeder::class,
            FacilitySessionSeeder::class,
            TestOrderSeeder::class,
            FacilityBookingSeeder::class,
            RadiologyLabNotificationTemplateSeeder::class,
        ]);
    }
}
