<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Models\Lab;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            $cairo = DB::table('governorates')->where('name_en', 'Cairo')->first();
            $alex = DB::table('governorates')->where('name_en', 'Alexandria')->first();
            $giza = DB::table('governorates')->where('name_en', 'Giza')->first();

            $cairoCities = $cairo ? DB::table('governorate_cities')->where('governorate_id', $cairo->id)->pluck('id', 'name_en')->toArray() : [];
            $alexCities = $alex ? DB::table('governorate_cities')->where('governorate_id', $alex->id)->pluck('id', 'name_en')->toArray() : [];
            $gizaCities = $giza ? DB::table('governorate_cities')->where('governorate_id', $giza->id)->pluck('id', 'name_en')->toArray() : [];

            $labs = [
                [
                    'name_ar' => 'معمل البرج',
                    'name_en' => 'Al-Borg Laboratory',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Nasr City'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'معمل المختبر',
                    'name_en' => 'Al-Mokhtabar Laboratory',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Heliopolis'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'معمل ألفا',
                    'name_en' => 'Alpha Lab',
                    'governorate_id' => $giza->id ?? null,
                    'city_id' => $gizaCities['Dokki'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'معمل النيل',
                    'name_en' => 'Nile Lab',
                    'governorate_id' => $alex->id ?? null,
                    'city_id' => $alexCities['Smouha'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'معمل كايرو سكان',
                    'name_en' => 'Cairo Scan Lab',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Fifth Settlement'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'معمل الحياة',
                    'name_en' => 'Al-Hayat Laboratory',
                    'governorate_id' => $giza->id ?? null,
                    'city_id' => $gizaCities['6th of October'] ?? null,
                    'status' => 0,
                ],
            ];

            foreach ($labs as $lab) {
                Lab::updateOrCreate(
                    ['name_en' => $lab['name_en']],
                    $lab
                );
            }
        }
    }
}
