<?php

namespace Modules\Constant\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Constant\Models\Constant;

class ConstantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /*
         * Constants Seed
         * ------------------
         */
        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $arr1 = [
            [
                'type' => 'PAYMENT_METHODS',
                'name' => 'cash',
                'value' => 'نقدي',
            ],
            [
                'type' => 'PAYMENT_METHODS',
                'name' => 'upi',
                'value' => 'UPI',
            ],
            [
                'type' => 'PAYMENT_METHODS',
                'name' => 'razorpay',
                'value' => 'Razorpay',
            ],
            [
                'type' => 'PAYMENT_METHODS',
                'name' => 'stripe',
                'value' => 'Stripe',
            ],
            [
                'type' => 'SERVICE_PROVIDER_SERVICE_GENDER',
                'name' => 'unisex',
                'value' => 'للجنسين',
            ],
            [
                'type' => 'SERVICE_PROVIDER_SERVICE_GENDER',
                'name' => 'female',
                'value' => 'أنثى',
            ],
            [
                'type' => 'SERVICE_PROVIDER_SERVICE_GENDER',
                'name' => 'male',
                'value' => 'ذكر',
            ],

            [
                'type' => 'status',
                'name' => 1,
                'value' => 'نشط',
            ],
            [
                'type' => 'status',
                'name' => 0,
                'value' => 'غير نشط',
            ],
            [
                'type' => 'BOOKING_STATUS',
                'name' => 'pending',
                'value' => 'قيد الانتظار',
                'sequence' => 0,
            ],
            [
                'type' => 'BOOKING_STATUS',
                'name' => 'confirmed',
                'value' => 'مؤكد',
                'sequence' => 1,
            ],
            [
                'type' => 'BOOKING_STATUS',
                'name' => 'check_in',
                'value' => 'تسجيل دخول',
                'sequence' => 2,
            ],
            [
                'type' => 'BOOKING_STATUS',
                'name' => 'checkout',
                'value' => 'تسجيل خروج',
                'sequence' => 3,
            ],
            [
                'type' => 'BOOKING_STATUS',
                'name' => 'cancelled',
                'value' => 'ملغي',
                'sequence' => 4,
            ],
            // [
            //     'type' => 'BOOKING_STATUS',
            //     'name' => 'completed',
            //     'value' => 'Completed',
            //     'sequence' => 5,
            // ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#e5a900',
                'value' => 'لون قيد الانتظار',
                'sub_type' => 'pending',
                'sequence' => 0,
            ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#6E6EEF',
                'value' => 'لون التأكيد',
                'sub_type' => 'confirmed',
                'sequence' => 1,
            ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#D68AF1',
                'value' => 'لون تسجيل الدخول',
                'sub_type' => 'check_in',
                'sequence' => 2,
            ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#E58282',
                'value' => 'لون تسجيل الخروج',
                'sub_type' => 'checkout',
                'sequence' => 3,
            ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#D1D1D1',
                'value' => 'لون الإلغاء',
                'sub_type' => 'cancelled',
                'sequence' => 4,
            ],
            [
                'type' => 'BOOKING_STATUS_COLOR',
                'name' => '#3ABA61',
                'value' => 'لون الاكتمال',
                'sub_type' => 'completed',
                'sequence' => 4,
            ],

            

            [
                'type' => 'field_type',
                'value' => 'نص',
                'name' => 'Text',
            ],

            [
                'type' => 'CLASS_TYPE_COLOR',
                'name' => '#e5a900',
                'value' => 'لون عدم التكرار',
                'sub_type' => 'does_not_repeat',
                'sequence' => 0,
            ],
            [
                'type' => 'CLASS_TYPE_COLOR',
                'name' => '#6E6EEF',
                'value' => 'لون يومي',
                'sub_type' => 'daily',
                'sequence' => 1,
            ],
            [
                'type' => 'CLASS_TYPE_COLOR',
                'name' => '#D68AF1',
                'value' => 'لون أسبوعي',
                'sub_type' => 'weekly',
                'sequence' => 2,
            ],
            [
                'type' => 'CLASS_TYPE_COLOR',
                'name' => '#E58282',
                'value' => 'لون شهري',
                'sub_type' => 'monthly',
                'sequence' => 3,
            ],
            [
                'type' => 'CLASS_TYPE_COLOR',
                'name' => '#D1D1D1',
                'value' => 'لون سنوي',
                'sub_type' => 'yearly',
                'sequence' => 4,
            ],
           
            [
                'type' => 'field_type',
                'value' => 'مساحة نص',
                'name' => 'Textarea',
                'sequence' => 2,
            ],
            [
                'type' => 'field_type',
                'value' => 'قائمة اختيار',
                'name' => 'Select',
                'sequence' => 3,
            ],
            [
                'type' => 'field_type',
                'value' => 'زر اختيار',
                'name' => 'Radio',
                'sequence' => 4,
            ],
            [
                'type' => 'field_type',
                'value' => 'مربع اختيار',
                'name' => 'Checkbox',
                'sequence' => 5,
            ],
            [
                'type' => 'language',
                'value' => 'en',
                'name' => 'English',
                'sequence' => 1,
            ],
            [
                'type' => 'language',
                'value' => 'br',
                'name' => 'বাংলা',
                'sequence' => 2,
            ],
            [
                'type' => 'language',
                'value' => 'ar',
                'name' => 'العربی',
                'sequence' => 3,
            ],
            [
                'type' => 'language',
                'value' => 'vi',
                'name' => 'Vietnamese',
                'sequence' => 4,
            ],
            [
                'type' => 'SLIDER_TYPES',
                'value' => 'category',
                'name' => 'تصنيف',
                'sequence' => 1,
            ],
            [
                'type' => 'SLIDER_TYPES',
                'value' => 'service',
                'name' => 'خدمة',
                'sequence' => 2,
            ],

            [
                'type' => 'EARNING_PAYMENT_TYPE',
                'value' => 'cash',
                'name' => 'نقدي',
                'sequence' => 1,
            ],

            [
                'type' => 'EARNING_PAYMENT_TYPE',
                'value' => 'bank',
                'name' => 'بنكي',
                'sequence' => 2,
            ],

            // [
            //     'type' => 'EARNING_PAYMENT_TYPE',
            //     'value' => 'wallet',
            //     'name' => 'Wallet',
            //     'sequence' => 3,
            // ],
            [
                'type' => 'additional_permissions',
                'value' => 'المعرض',
                'name' => 'gallery',
                'sequence' => 1,
            ],
            [
                'type' => 'additional_permissions',
                'value' => 'كلمة المرور',
                'name' => 'password',
                'sequence' => 2,
            ],
            [
                'type' => 'additional_permissions',
                'value' => 'عرض الجدول',
                'name' => 'tableview',
                'sequence' => 3,
            ],
            [
                'type' => 'additional_permissions',
                'value' => 'المراجعة',
                'name' => 'review',
                'sequence' => 4,
            ],
            [
                'type' => 'PAYMENT_STATUS',
                'name' => 'مدفوع',
                'value' => '1',
            ],

            [
                'type' => 'PAYMENT_STATUS',
                'name' => 'قيد الانتظار',
                'value' => '0',
            ],

            [
                'type' => 'encounter_problem',
                'value' => 'headache',
                'name' => 'صداع',
                'sequence' => 1,
            ],
            [
                'type' => 'encounter_problem',
                'value' => 'difficulty_breathing_or_wheezing',
                'name' => 'صعوبة في التنفس أو أزيز',
                'sequence' => 2,
            ],
            [
                'type' => 'encounter_problem',
                'value' => 'body_pain',
                'name' => 'آلام الجسم',
                'sequence' => 3,
            ],

            [
                'type' => 'encounter_observations',
                'value' => 'pulsing_or_throbbing_quality',
                'name' => 'نبض أو خفقان',
                'sequence' => 1,
            ],
            [
                'type' => 'encounter_observations',
                'value' => 'heart_pulse_rate ',
                'name' => 'معدل ضربات القلب/النبض',
                'sequence' => 2,
            ],
            [
                'type' => 'encounter_observations',
                'value' => 'blood_pressure',
                'name' => 'ضغط الدم',
                'sequence' => 3,
            ],
        ];

        foreach ($arr1 as $key => $val) {
            Constant::create($val);
        }
    }
}
