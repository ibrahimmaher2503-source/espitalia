<?php

namespace Modules\FrontendSetting\database\seeders;

use Illuminate\Database\Seeder;

class FrontendSettingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $sections = [
            [
                'id' => 1,
                'type' => 'landing-page-setting',
                'key' => 'section_1',
                'status' => 1,
                'value' => json_encode([
                    'section_1'        => 1,
                    'title'            => 'صحتك أولويتنا - احجز موعدك اليوم!',
                    'title_ar'         => 'صحتك أولويتنا - احجز موعدك اليوم!',
                    'title_en'         => 'Your Health is Our Priority - Book Today!',
                    'enable_search'    => 'on',
                    'instant_link'     => ['doctors', 'services', 'clinics'],
                    'enable_quick_booking' => 'on',
                ]),
            ],
            [
                'id' => 2,
                'type' => 'landing-page-setting',
                'key' => 'section_2',
                'status' => 1,
                'value' => json_encode([
                    'section_2' => 1,
                    "title"      => "التخصصات",
                    "title_ar"   => "التخصصات",
                    "title_en"   => "Specialties",
                    'subtitle'    => "اكتشف تخصصاتنا المتميزة",
                    'subtitle_ar' => "اكتشف تخصصاتنا المتميزة",
                    'subtitle_en' => "Discover Our Featured Specialties",
                    'category_id' => ['1', '2', '3', '4', '5'],
                ]),
            ],
            [
                'id' => 3,
                'type' => 'landing-page-setting',
                'key' => 'section_3',
                'status' => 1,
                'value' => json_encode([
                    'section_3'   => 1,
                    'title'       => 'الخدمات',
                    'title_ar'    => 'الخدمات',
                    'title_en'    => 'Services',
                    'subtitle'    => "خدماتنا الأكثر طلباً",
                    'subtitle_ar' => "خدماتنا الأكثر طلباً",
                    'subtitle_en' => "Our Most Requested Services",
                    'service_id'  => ['1', '2', '3', '4', '5', '6', '7', '8'],
                ]),
            ],
            [
                'id' => 4,
                'type' => 'landing-page-setting',
                'key' => 'section_4',
                'status' => 1,
                'value' => json_encode([
                    "section_4"      => 1,
                    "title"          => "ارتقِ بتجربتك المذهلة مع تطبيقنا بسهولة",
                    "title_ar"       => "ارتقِ بتجربتك المذهلة مع تطبيقنا بسهولة",
                    "title_en"       => "Elevate Your Experience with Our App",
                    "description"    => "من الحجز السهل إلى التوصيات الشخصية، نحن هنا لتحسين كل جانب من رحلتك.",
                    "description_ar" => "من الحجز السهل إلى التوصيات الشخصية، نحن هنا لتحسين كل جانب من رحلتك.",
                    "description_en" => "From easy booking to personalized recommendations, we're here to improve every aspect of your journey.",
                ]),
            ],
            [
                'id' => 5,
                'type' => 'landing-page-setting',
                'key' => 'section_5',
                'status' => 1,
                'value' => json_encode([
                    'section_5'   => 1,
                    'title'       => 'العيادات',
                    'title_ar'    => 'العيادات',
                    'title_en'    => 'Clinics',
                    'subtitle'    => 'اعثر على عيادتك المثالية',
                    'subtitle_ar' => 'اعثر على عيادتك المثالية',
                    'subtitle_en' => 'Find Your Perfect Clinic',
                    'clinic_id'   => ['1', '2', '3'],
                ]),
            ],
            [
                'id' => 6,
                'type' => 'landing-page-setting',
                'key' => 'section_6',
                'status' => 1,
                'value' => json_encode([
                    'section_6'   => 1,
                    'title'       => 'الأطباء',
                    'title_ar'    => 'الأطباء',
                    'title_en'    => 'Doctors',
                    'subtitle'    => "أطباؤنا المتميزون",
                    'subtitle_ar' => "أطباؤنا المتميزون",
                    'subtitle_en' => "Our Distinguished Doctors",
                    'doctor_id'   => ['1', '2', '3'],
                ]),
            ],
            [
                'id' => 7,
                'type' => 'landing-page-setting',
                'key' => 'section_7',
                'status' => 1,
                'value' => json_encode([
                    'section_7'      => 1,
                    'title'          => 'الأسئلة الشائعة',
                    'title_ar'       => 'الأسئلة الشائعة',
                    'title_en'       => 'FAQ',
                    'subtitle'       => 'الأسئلة المتكررة',
                    'subtitle_ar'    => 'الأسئلة المتكررة',
                    'subtitle_en'    => 'Frequently Asked Questions',
                    'description'    => 'اسأل أي شيء يتعلق بإسبيتاليا وسنقدم لك الإجابة مع الحل.',
                    'description_ar' => 'اسأل أي شيء يتعلق بإسبيتاليا وسنقدم لك الإجابة مع الحل.',
                    'description_en' => 'Ask anything about Espitalia and we will provide you with the answer and solution.',
                ]),
            ],
            [
                'id' => 8,
                'type' => 'landing-page-setting',
                'key' => 'section_8',
                'status' => 1,
                'value' => json_encode([
                    'section_8' => 1,
                ]),
            ],
            [
                'id' => 9,
                'type' => 'landing-page-setting',
                'key' => 'section_9',
                'status' => 1,
                'value' => json_encode([
                    'section_9'   => 1,
                    'title'       => 'المدونة',
                    'title_ar'    => 'المدونة',
                    'title_en'    => 'Blog',
                    'subtitle'    => "نصائح يومية للتذكر",
                    'subtitle_ar' => "نصائح يومية للتذكر",
                    'subtitle_en' => "Daily Tips to Remember",
                    'blog_id'     => [],
                ]),
            ],
            [
                'id' => 10,
                'type' => 'heder-menu-setting',
                'key' => 'heder-menu-setting',
                'status' => '1',
                'value' => json_encode([
                    "header_setting" => 1,
                    "categories" => 1,
                    "services" => 1,
                    "clinics" => 1,
                    "doctors" => 1,
                    "appointments" => 1,
                    "incidence" => 1,
                    "available_nurses" => true,
                    "lab_tests" => true,
                    "icu" => true,
                    "nurse_request" => true,
                    "doctor_visit" => true,
                    "enable_search" => 1,
                    "enable_language" => 1,
                    "enable_darknight_mode" => 1
                ]),
            ],
            [
                'id' => 11,
                'type' => 'footer-setting',
                'key' => 'footer-setting',
                'status' => '1',
                'value' => json_encode([
                    "footer_setting" => 1,
                    'enable_quick_link' => 1,
                    "enable_top_service" => 1,
                    "service_id" => ["1", "2", "3"],
                    "enable_top_category" => 1,
                    "category_id" => ["1", "2", "3", "4", "5", "6"],
                ]),
            ],
        ];

        // Clear the table before seeding
        \DB::table('frontend_settings')->truncate();

        // Insert all sections
        \DB::table('frontend_settings')->insert($sections);
    }
}
