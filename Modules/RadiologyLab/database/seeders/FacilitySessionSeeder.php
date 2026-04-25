<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Modules\RadiologyLab\Models\FacilitySession;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;

class FacilitySessionSeeder extends Seeder
{
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'saturday'];

            $labs = Lab::where('status', 1)->get();
            foreach ($labs as $lab) {
                foreach ($days as $day) {
                    FacilitySession::updateOrCreate(
                        [
                            'facility_type' => 'lab',
                            'facility_id' => $lab->id,
                            'day' => $day,
                        ],
                        [
                            'start_time' => '08:00',
                            'end_time' => '20:00',
                            'slot_duration' => 15,
                            'breaks' => [
                                ['start' => '13:00', 'end' => '14:00'],
                            ],
                            'is_holiday' => false,
                            'status' => true,
                        ]
                    );
                }

                FacilitySession::updateOrCreate(
                    [
                        'facility_type' => 'lab',
                        'facility_id' => $lab->id,
                        'day' => 'friday',
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '00:00',
                        'slot_duration' => 15,
                        'breaks' => [],
                        'is_holiday' => true,
                        'status' => false,
                    ]
                );
            }

            $centers = RadiologyCenter::where('status', 1)->get();
            foreach ($centers as $center) {
                foreach ($days as $day) {
                    FacilitySession::updateOrCreate(
                        [
                            'facility_type' => 'radiology',
                            'facility_id' => $center->id,
                            'day' => $day,
                        ],
                        [
                            'start_time' => '09:00',
                            'end_time' => '18:00',
                            'slot_duration' => 30,
                            'breaks' => [
                                ['start' => '13:00', 'end' => '13:30'],
                            ],
                            'is_holiday' => false,
                            'status' => true,
                        ]
                    );
                }

                FacilitySession::updateOrCreate(
                    [
                        'facility_type' => 'radiology',
                        'facility_id' => $center->id,
                        'day' => 'friday',
                    ],
                    [
                        'start_time' => '00:00',
                        'end_time' => '00:00',
                        'slot_duration' => 30,
                        'breaks' => [],
                        'is_holiday' => true,
                        'status' => false,
                    ]
                );
            }
        }
    }
}
