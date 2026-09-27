<?php

namespace Modules\Clinic\database\seeders;

use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorDocument;
use Modules\Clinic\Models\DoctorIndependentService;
use Modules\Clinic\Models\DoctorIndependentSession;
use Modules\Commission\Models\EmployeeCommission;

class IndependentDoctorSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        if (!env('IS_DUMMY_DATA')) {
            Schema::enableForeignKeyConstraints();
            return;
        }

        $avatarPath = config('app.avatar_base_path');

        $doctors = [
            [
                'first_name' => 'سارة',
                'last_name' => 'المنصوري',
                'email' => 'dr.sara.independent@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 5512345678',
                'date_of_birth' => '1985-03-15',
                'avatar' => $avatarPath . 'female.webp',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'user_type' => 'doctor',
                'about_self_ar' => 'طبيبة نفسية مستقلة متخصصة في علاج القلق والاكتئاب والاضطرابات النفسية',
                'about_self_en' => 'Independent psychiatrist specializing in treating anxiety, depression, and mental disorders',
                'expert_ar' => 'الطب النفسي',
                'expert_en' => 'Psychiatry',
                'address' => 'شارع الأمير سلطان، الرياض',
                'country' => 194,
                'state' => 3919,
                'city' => 42594,
                'pincode' => '12345',
                'latitude' => '24.7136',
                'longitude' => '46.6753',
                'vendor_id' => 1,
                'experience_ar' => '12 سنة',
                'experience_en' => '12 years',
                'title_ar' => 'دكتورة',
                'title_en' => 'Dr.',
                'degree' => 'دكتوراه في الطب النفسي',
                'university' => 'جامعة الملك سعود',
                'year' => '2009',
                'file_url' => public_path('/dummy-images/clinic/doctor/erica.png'),
                'services' => [
                    [
                        'name' => 'جلسة استشارية نفسية',
                        'description' => 'جلسة استشارية فردية لتقييم الحالة النفسية وتقديم خطة علاجية',
                        'duration_min' => 60,
                        'time_slot' => 60,
                        'charges' => 300.00,
                        'discount' => 0,
                        'status' => 1,
                    ],
                    [
                        'name' => 'جلسة علاج نفسي',
                        'description' => 'جلسة علاجية متخصصة باستخدام أساليب العلاج المعرفي السلوكي',
                        'duration_min' => 90,
                        'time_slot' => 90,
                        'charges' => 450.00,
                        'discount' => 1,
                        'discount_type' => 'percent',
                        'discount_value' => 10,
                        'status' => 1,
                    ],
                ],
            ],
            [
                'first_name' => 'خالد',
                'last_name' => 'الزهراني',
                'email' => 'dr.khalid.independent@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 5698741230',
                'date_of_birth' => '1980-07-22',
                'avatar' => $avatarPath . 'male.webp',
                'gender' => 'male',
                'email_verified_at' => Carbon::now(),
                'user_type' => 'doctor',
                'about_self_ar' => 'طبيب تغذية مستقل يقدم خطط غذائية مخصصة لتحسين الصحة العامة',
                'about_self_en' => 'Independent nutritionist providing personalized dietary plans to improve general health',
                'expert_ar' => 'علم التغذية والحمية',
                'expert_en' => 'Nutrition and Dietetics',
                'address' => 'شارع التحلية، جدة',
                'country' => 194,
                'state' => 3919,
                'city' => 42594,
                'pincode' => '12346',
                'latitude' => '21.4858',
                'longitude' => '39.1925',
                'vendor_id' => 1,
                'experience_ar' => '15 سنة',
                'experience_en' => '15 years',
                'title_ar' => 'دكتور',
                'title_en' => 'Dr.',
                'degree' => 'دكتوراه في علم التغذية',
                'university' => 'جامعة الملك عبدالعزيز',
                'year' => '2006',
                'file_url' => public_path('/dummy-images/clinic/doctor/felix.png'),
                'services' => [
                    [
                        'name' => 'استشارة تغذية أولى',
                        'description' => 'تقييم شامل للعادات الغذائية ووضع خطة غذائية مخصصة',
                        'duration_min' => 45,
                        'time_slot' => 45,
                        'charges' => 200.00,
                        'discount' => 0,
                        'status' => 1,
                    ],
                    [
                        'name' => 'متابعة نظام غذائي',
                        'description' => 'جلسة متابعة لمراجعة التقدم وتعديل الخطة الغذائية',
                        'duration_min' => 30,
                        'time_slot' => 30,
                        'charges' => 120.00,
                        'discount' => 1,
                        'discount_type' => 'fixed',
                        'discount_value' => 20,
                        'status' => 1,
                    ],
                    [
                        'name' => 'برنامج إنقاص الوزن',
                        'description' => 'برنامج متكامل لمدة شهر لإنقاص الوزن بأسلوب علمي سليم',
                        'duration_min' => 60,
                        'time_slot' => 60,
                        'charges' => 800.00,
                        'discount' => 1,
                        'discount_type' => 'percent',
                        'discount_value' => 15,
                        'status' => 1,
                    ],
                ],
            ],
            [
                'first_name' => 'نورة',
                'last_name' => 'الغامدي',
                'email' => 'dr.noura.independent@espitalia.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+966 5334567890',
                'date_of_birth' => '1988-11-05',
                'avatar' => $avatarPath . 'female.webp',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'user_type' => 'doctor',
                'about_self_ar' => 'طبيبة جلدية مستقلة متخصصة في علاج أمراض الجلد والتجميل الطبي',
                'about_self_en' => 'Independent dermatologist specializing in skin diseases and medical aesthetics',
                'expert_ar' => 'الأمراض الجلدية والتجميل',
                'expert_en' => 'Dermatology and Medical Aesthetics',
                'address' => 'شارع العليا، الرياض',
                'country' => 194,
                'state' => 3919,
                'city' => 42594,
                'pincode' => '12347',
                'latitude' => '24.6877',
                'longitude' => '46.7219',
                'vendor_id' => 1,
                'experience_ar' => '8 سنوات',
                'experience_en' => '8 years',
                'title_ar' => 'دكتورة',
                'title_en' => 'Dr.',
                'degree' => 'ماجستير في الأمراض الجلدية',
                'university' => 'جامعة الأميرة نورة',
                'year' => '2014',
                'file_url' => public_path('/dummy-images/clinic/doctor/parsa.png'),
                'services' => [
                    [
                        'name' => 'استشارة جلدية',
                        'description' => 'فحص وتشخيص أمراض الجلد وتقديم العلاج المناسب',
                        'duration_min' => 30,
                        'time_slot' => 30,
                        'charges' => 250.00,
                        'discount' => 0,
                        'status' => 1,
                    ],
                    [
                        'name' => 'جلسة تقشير كيميائي',
                        'description' => 'تقشير كيميائي متخصص لتجديد البشرة وإزالة التصبغات',
                        'duration_min' => 45,
                        'time_slot' => 45,
                        'charges' => 400.00,
                        'discount' => 1,
                        'discount_type' => 'percent',
                        'discount_value' => 5,
                        'status' => 1,
                    ],
                ],
            ],
        ];

        $days = [
            ['day' => 'monday',    'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'breaks' => [['start_break' => '13:00', 'end_break' => '14:00']]],
            ['day' => 'tuesday',   'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'breaks' => [['start_break' => '13:00', 'end_break' => '14:00']]],
            ['day' => 'wednesday', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'breaks' => [['start_break' => '13:00', 'end_break' => '14:00']]],
            ['day' => 'thursday',  'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'breaks' => [['start_break' => '13:00', 'end_break' => '14:00']]],
            ['day' => 'friday',    'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => false, 'breaks' => [['start_break' => '12:00', 'end_break' => '14:00']]],
            ['day' => 'saturday',  'start_time' => '10:00:00', 'end_time' => '15:00:00', 'is_holiday' => false, 'breaks' => []],
            ['day' => 'sunday',    'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_holiday' => true,  'breaks' => []],
        ];

        foreach ($doctors as $data) {
            $image = $data['file_url'] ?? null;
            $userData = Arr::except($data, [
                'file_url', 'about_self_ar', 'about_self_en', 'expert_ar', 'expert_en',
                'vendor_id', 'experience_ar', 'experience_en', 'title_ar', 'title_en',
                'degree', 'university', 'year', 'services',
            ]);

            $user = User::withTrashed()->where('email', $data['email'])->first();

            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }

                $user->fill($userData);
                $user->save();
            } else {
                $user = User::create($userData);
            }

            if (! $user->hasRole('doctor')) {
                $user->assignRole('doctor');
            }

            if ($image && ! $user->getFirstMedia('profile_image')) {
                $this->attachFeatureImage($user, $image);
            }

            UserProfile::updateOrCreate(['user_id' => $user->id], [
                'user_id'      => $user->id,
                'about_self'   => $data['about_self_ar'],
                'about_self_ar' => $data['about_self_ar'],
                'about_self_en' => $data['about_self_en'],
                'expert'       => $data['expert_ar'],
                'expert_ar'    => $data['expert_ar'],
                'expert_en'    => $data['expert_en'],
            ]);

            DoctorDocument::updateOrCreate(['doctor_id' => $user->id], [
                'doctor_id'  => $user->id,
                'degree'     => $data['degree'],
                'university' => $data['university'],
                'year'       => $data['year'],
            ]);

            Doctor::updateOrCreate(['doctor_id' => $user->id], [
                'doctor_id'      => $user->id,
                'vendor_id'      => $data['vendor_id'],
                'experience'     => $data['experience_ar'],
                'experience_ar'  => $data['experience_ar'],
                'experience_en'  => $data['experience_en'],
                'title_ar'       => $data['title_ar'],
                'title_en'       => $data['title_en'],
                'is_independent' => true,
                'is_active'      => 1,
            ]);

            EmployeeCommission::firstOrCreate([
                'employee_id'   => $user->id,
                'commission_id' => 2,
            ]);

            // Independent sessions (schedule)
            foreach ($days as $day) {
                DoctorIndependentSession::updateOrCreate([
                    'doctor_id'  => $user->id,
                    'day'        => $day['day'],
                ], [
                    'doctor_id'  => $user->id,
                    'day'        => $day['day'],
                    'start_time' => $day['start_time'],
                    'end_time'   => $day['end_time'],
                    'is_holiday' => $day['is_holiday'],
                    'breaks'     => $day['breaks'],
                    'status'     => 1,
                ]);
            }

            // Independent services
            foreach ($data['services'] as $service) {
                DoctorIndependentService::updateOrCreate([
                    'doctor_id' => $user->id,
                    'name'      => $service['name'],
                ], array_merge($service, [
                    'doctor_id' => $user->id,
                ]));
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function attachFeatureImage($model, $publicPath): void
    {
        if (!env('IS_DUMMY_DATA_IMAGE')) {
            return;
        }

        if (!file_exists($publicPath)) {
            return;
        }

        $file = new \Illuminate\Http\File($publicPath);
        $model->addMedia($file)->preservingOriginal()->toMediaCollection('profile_image');
    }
}
