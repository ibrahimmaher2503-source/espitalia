<?php

namespace Modules\Offer\Database\Seeders;

use Illuminate\Database\Seeder;

class OfferDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OfferPermissionSeeder::class,
            OfferSeeder::class,
        ]);
    }
}
