<?php

namespace Modules\RequestNurse\database\seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\RequestNurse\Models\Nurse;

class NurseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        if (env('IS_DUMMY_DATA')) {
            $nurses = [
                [
                    'first_name' => 'فاطمة',
                    'last_name' => 'الزهراني',
                    'email' => 'fatima.nurse@espitalia.com',
                    'password' => Hash::make('12345678'),
                    'mobile' => '+966 551234567',
                    'gender' => 'female',
                    'user_type' => 'nurse',
                    'country' => 194,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'nurse' => [
                        'specialization_ar' => 'تمريض العناية المركزة',
                        'specialization_en' => 'ICU Nursing',
                        'experience_ar' => '8 سنوات',
                        'experience_en' => '8 years',
                        'about_ar' => 'ممرضة متخصصة في العناية المركزة مع خبرة واسعة في رعاية المرضى الحرجين ومتابعة الحالات الطبية المعقدة في وحدات العناية المركزة.',
                        'about_en' => 'Nurse specialized in intensive care with extensive experience in critical patient care and monitoring complex medical cases in ICUs.',
                        'availability_status' => 'available',
                        'service_area_ar' => 'الرياض',
                        'service_area_en' => 'Riyadh',
                        'hourly_rate' => 200,
                        'vendor_id' => 1,
                        'status' => 1,
                    ],
                ],
                [
                    'first_name' => 'نورة',
                    'last_name' => 'القحطاني',
                    'email' => 'noura.nurse@espitalia.com',
                    'password' => Hash::make('12345678'),
                    'mobile' => '+966 552345678',
                    'gender' => 'female',
                    'user_type' => 'nurse',
                    'country' => 194,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'nurse' => [
                        'specialization_ar' => 'تمريض الأطفال',
                        'specialization_en' => 'Pediatric Nursing',
                        'experience_ar' => '5 سنوات',
                        'experience_en' => '5 years',
                        'about_ar' => 'ممرضة متخصصة في رعاية الأطفال وحديثي الولادة مع مهارات عالية في التعامل مع الحالات الطارئة للأطفال وتقديم الرعاية المنزلية.',
                        'about_en' => 'Nurse specialized in pediatric and neonatal care with strong skills in handling pediatric emergencies and providing home nursing.',
                        'availability_status' => 'available',
                        'service_area_ar' => 'الرياض - جدة',
                        'service_area_en' => 'Riyadh - Jeddah',
                        'hourly_rate' => 180,
                        'vendor_id' => 1,
                        'status' => 1,
                    ],
                ],
                [
                    'first_name' => 'أحمد',
                    'last_name' => 'الشمري',
                    'email' => 'ahmed.nurse@espitalia.com',
                    'password' => Hash::make('12345678'),
                    'mobile' => '+966 553456789',
                    'gender' => 'male',
                    'user_type' => 'nurse',
                    'country' => 194,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'nurse' => [
                        'specialization_ar' => 'تمريض الطوارئ',
                        'specialization_en' => 'Emergency Nursing',
                        'experience_ar' => '10 سنوات',
                        'experience_en' => '10 years',
                        'about_ar' => 'ممرض متخصص في طب الطوارئ والإسعافات الأولية مع خبرة في التعامل مع الحالات الحرجة والطوارئ الطبية المنزلية.',
                        'about_en' => 'Nurse specialized in emergency medicine and first aid with experience handling critical cases and home medical emergencies.',
                        'availability_status' => 'busy',
                        'service_area_ar' => 'الرياض',
                        'service_area_en' => 'Riyadh',
                        'hourly_rate' => 250,
                        'vendor_id' => 1,
                        'status' => 1,
                    ],
                ],
                [
                    'first_name' => 'سارة',
                    'last_name' => 'العتيبي',
                    'email' => 'sara.nurse@espitalia.com',
                    'password' => Hash::make('12345678'),
                    'mobile' => '+966 554567890',
                    'gender' => 'female',
                    'user_type' => 'nurse',
                    'country' => 194,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'nurse' => [
                        'specialization_ar' => 'تمريض كبار السن',
                        'specialization_en' => 'Geriatric Nursing',
                        'experience_ar' => '6 سنوات',
                        'experience_en' => '6 years',
                        'about_ar' => 'ممرضة متخصصة في رعاية كبار السن وتقديم الخدمات التمريضية المنزلية للمسنين مع خبرة في التعامل مع الأمراض المزمنة.',
                        'about_en' => 'Nurse specialized in elderly care and home nursing for seniors with experience managing chronic diseases.',
                        'availability_status' => 'available',
                        'service_area_ar' => 'جدة',
                        'service_area_en' => 'Jeddah',
                        'hourly_rate' => 190,
                        'vendor_id' => 3,
                        'status' => 1,
                    ],
                ],
            ];

            foreach ($nurses as $nurseData) {
                $nurseProfile = $nurseData['nurse'];
                $userData = Arr::except($nurseData, ['nurse']);

                $user = User::create($userData);
                $user->assignRole('nurse');

                Nurse::create([
                    'nurse_id' => $user->id,
                    'vendor_id' => $nurseProfile['vendor_id'],
                    'specialization_ar' => $nurseProfile['specialization_ar'],
                    'specialization_en' => $nurseProfile['specialization_en'],
                    'experience_ar' => $nurseProfile['experience_ar'],
                    'experience_en' => $nurseProfile['experience_en'],
                    'about_ar' => $nurseProfile['about_ar'],
                    'about_en' => $nurseProfile['about_en'],
                    'availability_status' => $nurseProfile['availability_status'],
                    'service_area_ar' => $nurseProfile['service_area_ar'],
                    'service_area_en' => $nurseProfile['service_area_en'],
                    'hourly_rate' => $nurseProfile['hourly_rate'],
                    'status' => $nurseProfile['status'],
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
