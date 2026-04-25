<?php

namespace Modules\RequestNurse\database\seeders;

use Illuminate\Database\Seeder;

class RequestNurseDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            NurseSeeder::class,
            NurseRequestSeeder::class,
        ]);
    }
}
