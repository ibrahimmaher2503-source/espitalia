<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\File;
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('production') || ! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Fixture settings are disabled outside local and testing environments.');
            return;
        }

        $directory = storage_path('app/data');
        $jsonFiles = File::glob($directory . '/*.json');

        foreach ($jsonFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
        $modules = [
            [
                'name' => 'default_time_zone',
                'val' => 'Asia/Riyadh',
                'type' =>'misc',
                'datatype' => 'misc',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'is_application_link',
                'val' => 1,
                'type' =>'integaration',
            ],
            [
                'name' => 'patient_app_play_store',
                'val' => 'https://play.google.com/store/apps/details?id=com.wellness.customer',
                'type' =>'is_application_link',
            ],
            [
                'name' => 'patient_app_app_store',
                'val' => 'https://apps.apple.com/in/app/kivicare-clinic-admin-app/id6502933651',
                'type' =>'is_application_link',
            ],
            [
                'name' => 'clinicadmin_app_play_store',
                'val' => 'https://play.google.com/store/apps/details?id=com.wellness.vendor',
                'type' =>'is_application_link',
            ],
            [
                'name' => 'clinicadmin_app_app_store',
                'val' => 'https://apps.apple.com/in/app/kivicare-employee-app/id6502933651',
                'type' =>'is_application_link',
            ],
            [
                'name' => 'is_zoom',
                'val' => 1,
                'type' =>'integaration',
            ],
            [
                'name' => 'account_id',
                'val' => env('ZOOM_ACCOUNT_ID', ''),
                'type' =>'is_zoom',
            ],
            [
                'name' => 'client_id',
                'val' => env('ZOOM_CLIENT_ID', ''),
                'type' =>'is_zoom',
            ],
            [
                'name' => 'client_secret',
                'val' => env('ZOOM_CLIENT_SECRET', ''),
                'type' =>'is_zoom',
            ],

            [
                'name' => 'razor_payment_method',
                'val' => 1,
                'type' =>'razorpayPayment',
            ],
            [
                'name' => 'razorpay_secretkey',
                'val' => env('RAZORPAY_SECRET', ''),
                'type' =>'razor_payment_method',
            ],
            [
                'name' => 'razorpay_publickey',
                'val' => env('RAZORPAY_PUBLIC_KEY', ''),
                'type' =>'razor_payment_method',
            ],
            [
                'name' => 'str_payment_method',
                'val' => 1,
                'type' =>'stripePayment',
            ],
            [
                'name' => 'stripe_secretkey',
                'val' => env('STRIPE_SECRET', ''),
                'type' =>'str_payment_method',
            ],
            [
                'name' => 'stripe_publickey',
                'val' => env('STRIPE_PUBLIC_KEY', ''),
                'type' =>'str_payment_method',
            ],
            [
                'name' => 'paystack_payment_method',
                'val' => 1,
                'type' =>'paystackPayment',
            ],
            [
                'name' => 'paystack_secretkey',
                'val' => env('PAYSTACK_SECRET', ''),
                'type' =>'paystack_payment_method',
            ],
            [
                'name' => 'paystack_publickey',
                'val' => env('PAYSTACK_PUBLIC_KEY', ''),
                'type' =>'paystack_payment_method',
            ],
            [
                'name' => 'paypal_payment_method',
                'val' => 1,
                'type' =>'paypalPayment',
            ],
            [
                'name' => 'paypal_secretkey',
                'val' => env('PAYPAL_SECRET', ''),
                'type' =>'paypal_payment_method',
            ],
            [
                'name' => 'paypal_clientid',
                'val' => env('PAYPAL_CLIENT_ID', ''),
                'type' =>'paypal_payment_method',
            ],
            [
                'name' => 'flutterwave_payment_method',
                'val' => 1,
                'type' =>'flutterwavePayment',
            ],
            [
                'name' => 'flutterwave_secretkey',
                'val' => env('FLUTTERWAVE_SECRET', ''),
                'type' =>'flutterwave_payment_method',
            ],
            [
                'name' => 'flutterwave_publickey',
                'val' => env('FLUTTERWAVE_PUBLIC_KEY', ''),
                'type' =>'flutterwave_payment_method',
            ],
            [
                'name' => 'is_event',
                'val' => 1,
                'type' =>'other_settings',
            ],
            [
                'name' => 'is_blog',
                'val' => 1,
                'type' =>'other_settings',
            ],
            [
                'name' => 'is_user_push_notification',
                'val' => 1,
                'type' =>'other_settings',
            ],
            [
                'name' => 'is_provider_push_notification',
                'val' => 1,
                'type' =>'other_settings',
            ],

            [
                'name' => 'firebase_notification',
                'val' => '1',
                'type' =>'other_settings',
            ],
            [
                'name' => 'firebase_project_id',
                'val' => env('FIREBASE_PROJECT_ID', ''),
                'type' =>'firebase_notification',
            ],

            [
                'name' => 'view_patient_soap',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_body_chart',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_telemed_setting',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_multi_vendor',
                'val' => '0',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_encounter_problem',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_encounter_observation',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_encounter_note',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'is_encounter_prescription',
                'val' => '1',
                'type' =>'module_settings',
            ],
            [
                'name' => 'date_formate',
                'val' => 'Y-m-d',
                'type' =>'misc',
                'datatype' => 'misc',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'time_formate',
                'val' => 'g:i A',
                'type' =>'misc',
                'datatype' => 'misc',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'theme_mode',
                'val' => 'whiteTheme',
                'type' =>'string',
            ],
            [
                'name' => 'Menubar_position',
                'val' => 'top',
                'type' =>'string',
            ],
            [
                'name' => 'image_handling',
                'val' => 'new_image',
                'type' =>'string',
            ],
            [
                'name' => 'menu_items',
                'val' => 'crop,rotate,flip,draw,shape,icon,text,mask',
                'type' =>'array',
            ],
            [
                'name' => 'clinic',
                'val' => 1,
                'type' =>'misc',
                'datatype' => 'misc',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'helpline_number',
                'val' => '920012345',
                'type' =>'bussiness', 
                'datatype' => 'bussiness',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'inquriy_email',
                'val' => 'info@espitalia.sa',
                'type' =>'bussiness', 
                'datatype' => 'bussiness',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'short_description',
                'val' => 'إسبيتاليا: تبسيط المواعيد الطبية عبر الإنترنت. ابحث عن الأطباء، احجز مواعيدك، وأدِر صحتك بسهولة.',
                'type' =>'bussiness', 
                'datatype' => 'bussiness',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'is_google_login',
                'val' => 1,
                'type' => 'integaration',
                'datatype' => '',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'is_dummy_credentials',
                'val' => 0,
                'type' =>'integaration', 
                'datatype' => '',
                'created_by' => 1,
                'updated_by' => 1,
            ],

        ];
        foreach ($modules as $key => $value) {

            $service = [
                'name' => $value['name'],
                'val' => $value['val'],
                'type' => $value['type'],
                'datatype' => $value['datatype'] ?? null,
                'created_by' => 1,
                'updated_by' => 1,
            ];
            $service = Setting::create($service);
        }

    }
}
