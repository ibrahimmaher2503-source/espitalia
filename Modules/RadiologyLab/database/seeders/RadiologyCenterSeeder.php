<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\RadiologyLab\Models\RadiologyCenter;

class RadiologyCenterSeeder extends Seeder
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

            $centers = [
                [
                    'name_ar' => 'مركز النيل للأشعة',
                    'name_en' => 'Nile Radiology Center',
                    'scan_type' => 'x-ray',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Nasr City'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'مركز الدلتا للرنين المغناطيسي',
                    'name_en' => 'Delta MRI Center',
                    'scan_type' => 'mri',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Heliopolis'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'مركز الأهرام للأشعة المقطعية',
                    'name_en' => 'Ahram CT Scan Center',
                    'scan_type' => 'ct',
                    'governorate_id' => $giza->id ?? null,
                    'city_id' => $gizaCities['Mohandessin'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'مركز المتحدة للموجات فوق الصوتية',
                    'name_en' => 'United Ultrasound Center',
                    'scan_type' => 'ultrasound',
                    'governorate_id' => $alex->id ?? null,
                    'city_id' => $alexCities['Sidi Gaber'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'مركز القاهرة التشخيصي',
                    'name_en' => 'Cairo Diagnostic Center',
                    'scan_type' => 'mri',
                    'governorate_id' => $cairo->id ?? null,
                    'city_id' => $cairoCities['Fifth Settlement'] ?? null,
                    'status' => 1,
                ],
                [
                    'name_ar' => 'مركز الشفاء للأشعة',
                    'name_en' => 'Al-Shefa Radiology Center',
                    'scan_type' => 'x-ray',
                    'governorate_id' => $giza->id ?? null,
                    'city_id' => $gizaCities['6th of October'] ?? null,
                    'status' => 0,
                ],
            ];

            foreach ($centers as $center) {
                RadiologyCenter::updateOrCreate(
                    ['name_en' => $center['name_en']],
                    $center
                );
            }
        }
    }
}
