<?php

namespace Modules\Clinic\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Clinic\Models\Clinics;
use Modules\Service\Models\SystemServiceCategory;
use Illuminate\Support\Arr;
use Modules\Clinic\Models\ClinicSession;

class ClinicTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (env('IS_DUMMY_DATA')) {
            $data = [
                [
                    'slug'=>'heartcare-orthocare-center',
                    'name' => ['ar' => 'مركز رعاية القلب والعظام', 'en' => 'HeartCare & OrthoCare Center'],
                    'email'=>'heartcare_orthocare_center@gmail.com',
                    'system_service_category' => 'specialist-clinics',
                    'description' => ['ar' => 'خدمات طبية متخصصة مصممة لتلبية الاحتياجات الصحية المحددة', 'en' => 'Specialized medical services tailored to specific health needs'],
                    'contact_number'=>'1-63852598',
                    'address'=>'شارع الملك فهد 349',
                    'country' => 194,
                    'state' => 3919,
                    'city' => 42594,
                    'pincode' => '12345',
                    'latitude'=>'40.7128',
                    'longitude'=>'74.006',
                    'time_slot' => 5,
                    'status'=> 1,
                    'vendor_id'=> 1,
                    'file_url' => public_path('/dummy-images/clinic/center/heartcare_orthocare_center.png'),
                ],
                [
                    'slug'=>'harmony-medical-center',
                    'name' => ['ar' => 'مركز الوئام الطبي', 'en' => 'Harmony Medical Center'],
                    'email'=>'harmony_medical_center@gmail.com',
                    'system_service_category' => 'general-medicine',
                    'description' => ['ar' => 'رعاية صحية شاملة للأمراض الشائعة والرعاية الوقائية', 'en' => 'Comprehensive healthcare for common illnesses and preventive care'],
                    'contact_number'=>'1-52638596',
                    'address'=>'شارع الأمير سلطان 178',
                    'country' => 194,
                    'state' => 3919,
                    'city' => 42594,
                    'pincode' => '12345',
                    'latitude'=>'40.7128',
                    'longitude'=>'74.006',
                    'time_slot' => 5,
                    'status'=> 1,
                    'vendor_id'=> 1,
                    'file_url' => public_path('/dummy-images/clinic/center/harmony_medical_center.png'),
                ],
                [
                    'slug'=>'wellness-dental-clinic',
                    'name' => ['ar' => 'عيادة العافية لطب الأسنان', 'en' => 'Wellness Dental Clinic'],
                    'email'=>'wellness_dental_clinic@gmail.com',
                    'system_service_category' => 'dental-care',
                    'description' => ['ar' => 'خدمات متخصصة في صحة ونظافة الفم والأسنان', 'en' => 'Services focused on oral health and hygiene'],
                    'contact_number'=>'1-78538597',
                    'address'=>'شارع التحلية 466',
                    'country' => 194,
                    'state' => 3919,
                    'city' => 42594,
                    'pincode' => '12345',
                    'latitude'=>'40.7128',
                    'longitude'=>'74.006',
                    'time_slot' => 5,
                    'status'=> 1,
                    'vendor_id'=> 1,
                    'file_url' => public_path('/dummy-images/clinic/center/wellness_dental_clinic.png'),
                ],
                [
                    'slug'=>'dermatology-associates',
                    'name' => ['ar' => 'مركز شركاء الجلدية', 'en' => 'Dermatology Associates'],
                    'email'=>'dermatology_associates@gmail.com',
                    'system_service_category' => 'specialist-clinics',
                    'description' => ['ar' => 'رعاية طبية متخصصة يقدمها أطباء خبراء في مختلف التخصصات', 'en' => 'Specialized medical care provided by expert clinicians'],
                    'contact_number'=>'1-58592639',
                    'address'=>'شارع العليا 901',
                    'country' => 194,
                    'state' => 3919,
                    'city' => 42594,
                    'pincode' => '12345',
                    'latitude'=>'40.7128',
                    'longitude'=>'74.006',
                    'time_slot' => 5,
                    'status'=> 1,
                    'vendor_id'=> 3,
                    'file_url' => public_path('/dummy-images/clinic/center/dermatology_associates.png'),
                ],
                [
                    'slug'=>'visionPlus-eye-clinic',
                    'name' => ['ar' => 'عيادة رؤية بلس للعيون', 'en' => 'VisionPlus Eye Clinic'],
                    'email'=>'vision_plus_eye_clinic@gmail.com',
                    'system_service_category' => 'specialist-clinics',
                    'description' => ['ar' => 'خدمات صحية متخصصة تقدم أحدث العلاجات وخطط رعاية شخصية', 'en' => 'Specialized healthcare services offering cutting-edge treatments'],
                    'contact_number'=>'1-26385601',
                    'address'=>'شارع الأمير محمد بن عبدالعزيز 561',
                    'country' => 194,
                    'state' => 3919,
                    'city' => 42594,
                    'pincode' => '12345',
                    'latitude'=>'40.7128',
                    'longitude'=>'74.006',
                    'time_slot' => 5,
                    'status'=> 1,
                    'vendor_id'=> 4,
                    'file_url' => public_path('/dummy-images/clinic/center/vision_plus_eye_clinic.png'),
                ],

            ];

            $days = [
                ['day' => 'monday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'tuesday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'wednesday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'thursday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'friday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'saturday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => false, 'breaks' => []],
                ['day' => 'sunday', 'start_time' => '09:00:00', 'end_time' => '18:00:00', 'is_holiday' => true, 'breaks' => []],
            ];

            foreach ($data as $key => $val) {
                $system_service_category = SystemServiceCategory::where('slug',$val['system_service_category'])->first();
                $featureImage = $val['file_url'] ?? null;
                $originalSlug = $val['slug'];
                $clinicArray = Arr::except($val, [ 'file_url']);
                $clinicArray['system_service_category'] = $system_service_category->id;
                $clinicData = Clinics::create($clinicArray);
                // Restore slug that may have been overwritten by HasSlug trait
                $clinicData->slug = $originalSlug;
                $clinicData->saveQuietly();
                if (isset($featureImage)) {
                    $this->attachFeatureImage($clinicData, $featureImage);
                }

                foreach ($days as $key => $val) {

                    $val['clinic_id'] = $clinicData->id;
                   
                    ClinicSession::create($val);
                }

            }
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
    private function attachFeatureImage($model, $publicPath)
    {
        if(!env('IS_DUMMY_DATA_IMAGE')) return false;

        $file = new \Illuminate\Http\File($publicPath);

        $media = $model->addMedia($file)->preservingOriginal()->toMediaCollection('file_url');

        return $media;

    }
}
