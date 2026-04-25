<?php

namespace Modules\Specialization\database\seeders;

use Illuminate\Database\Seeder;

class SpecializationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SpecializationPermissionSeeder::class,
        ]);
    }
}
