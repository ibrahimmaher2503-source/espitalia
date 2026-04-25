<?php

namespace Modules\Location\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Location\Models\Location;

class LocationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Location::firstOrCreate(
            ['is_default' => 1],
            [
                'name' => 'Default Location',
                'address_line_1' => 'Default Address',
                'status' => 1,
            ]
        );

        $this->call(GovernorateSeeder::class);
    }
}
