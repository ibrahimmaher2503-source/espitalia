<?php

namespace Modules\RequestNurse\database\seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;

class NurseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        if (env('IS_DUMMY_DATA')) {
            $nurses = Nurse::orderBy('id')->get();

            if ($nurses->count() < 4) {
                return;
            }

            $nurse1 = $nurses[0];
            $nurse2 = $nurses[1];
            $nurse3 = $nurses[2];
            $nurse4 = $nurses[3];

            $requests = [
                [
                    'patient_id' => 9,
                    'nurse_id' => $nurse1->nurse_id,
                    'service_description_ar' => 'رعاية ما بعد العملية الجراحية',
                    'service_description_en' => 'Post-surgical care',
                    'request_date' => Carbon::now()->subDays(10)->toDateString(),
                    'preferred_date' => Carbon::now()->subDays(9)->toDateString(),
                    'preferred_time' => '08:00:00',
                    'duration_hours' => 8,
                    'address_line_1' => 'شارع الملك فهد',
                    'address_line_2' => 'حي العليا، مبنى رقم 45',
                    'city' => 'الرياض',
                    'state' => 'منطقة الرياض',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '11564',
                    'latitude' => 24.7136,
                    'longitude' => 46.6753,
                    'contact_number' => '+966 551112233',
                    'status' => 'completed',
                    'payment_status' => 1,
                    'total_amount' => 1600,
                    'patient_notes' => 'المريض يحتاج رعاية بعد عملية جراحية في البطن، يرجى إحضار معدات تغيير الضمادات.',
                    'admin_notes' => 'تم إكمال الخدمة بنجاح وتقييم الممرضة ممتاز.',
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ],
                [
                    'patient_id' => 10,
                    'nurse_id' => $nurse2->nurse_id,
                    'service_description_ar' => 'رعاية طفل مريض بالمنزل',
                    'service_description_en' => 'Home care for a sick child',
                    'request_date' => Carbon::now()->subDays(3)->toDateString(),
                    'preferred_date' => Carbon::now()->subDays(2)->toDateString(),
                    'preferred_time' => '10:00:00',
                    'duration_hours' => 4,
                    'address_line_1' => 'شارع التحلية',
                    'address_line_2' => 'حي السليمانية، فيلا رقم 12',
                    'city' => 'الرياض',
                    'state' => 'منطقة الرياض',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '11543',
                    'latitude' => 24.6938,
                    'longitude' => 46.6850,
                    'contact_number' => '+966 552223344',
                    'status' => 'confirmed',
                    'payment_status' => 0,
                    'total_amount' => 720,
                    'patient_notes' => 'طفل عمره 3 سنوات مصاب بحمى وسعال، يحتاج متابعة ورعاية منزلية.',
                    'admin_notes' => 'تم تأكيد الطلب وإبلاغ الممرضة.',
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ],
                [
                    'patient_id' => 11,
                    'nurse_id' => $nurse3->nurse_id,
                    'service_description_ar' => 'متابعة حالة طوارئ منزلية',
                    'service_description_en' => 'Home emergency follow-up',
                    'request_date' => Carbon::now()->subDays(1)->toDateString(),
                    'preferred_date' => Carbon::now()->toDateString(),
                    'preferred_time' => '07:00:00',
                    'duration_hours' => 6,
                    'address_line_1' => 'شارع الأمير سلطان',
                    'address_line_2' => 'حي الروضة، شقة رقم 8',
                    'city' => 'الرياض',
                    'state' => 'منطقة الرياض',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '11523',
                    'latitude' => 24.7253,
                    'longitude' => 46.7100,
                    'contact_number' => '+966 553334455',
                    'status' => 'in_progress',
                    'payment_status' => 1,
                    'total_amount' => 1500,
                    'patient_notes' => 'مريض يعاني من صعوبة في التنفس ويحتاج متابعة مستمرة مع قياس الأكسجين.',
                    'admin_notes' => 'حالة طارئة، تم إرسال الممرض فوراً.',
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ],
                [
                    'patient_id' => 12,
                    'nurse_id' => $nurse1->nurse_id,
                    'service_description_ar' => 'رعاية مريض سكري',
                    'service_description_en' => 'Diabetic patient care',
                    'request_date' => Carbon::now()->toDateString(),
                    'preferred_date' => Carbon::now()->addDays(2)->toDateString(),
                    'preferred_time' => '09:00:00',
                    'duration_hours' => 5,
                    'address_line_1' => 'شارع الملك عبدالعزيز',
                    'address_line_2' => 'حي الملز، منزل رقم 22',
                    'city' => 'الرياض',
                    'state' => 'منطقة الرياض',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '11411',
                    'latitude' => 24.6600,
                    'longitude' => 46.7100,
                    'contact_number' => '+966 554445566',
                    'status' => 'pending',
                    'payment_status' => 0,
                    'total_amount' => 1000,
                    'patient_notes' => 'مريض سكري يحتاج متابعة قياس السكر وحقن الإنسولين يومياً.',
                    'admin_notes' => null,
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ],
                [
                    'patient_id' => 13,
                    'nurse_id' => $nurse4->nurse_id,
                    'service_description_ar' => 'رعاية كبار السن',
                    'service_description_en' => 'Elderly care',
                    'request_date' => Carbon::now()->subDays(5)->toDateString(),
                    'preferred_date' => Carbon::now()->subDays(4)->toDateString(),
                    'preferred_time' => '08:00:00',
                    'duration_hours' => 8,
                    'address_line_1' => 'شارع فلسطين',
                    'address_line_2' => 'حي الحمراء، فيلا رقم 7',
                    'city' => 'جدة',
                    'state' => 'منطقة مكة المكرمة',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '21452',
                    'latitude' => 21.5433,
                    'longitude' => 39.1728,
                    'contact_number' => '+966 555556677',
                    'status' => 'cancelled',
                    'payment_status' => 0,
                    'total_amount' => 1520,
                    'patient_notes' => 'رعاية والد مسن يعاني من صعوبة في الحركة.',
                    'admin_notes' => 'تم إلغاء الطلب بناءً على رغبة المريض.',
                    'cancelled_by' => 'patient',
                    'cancellation_reason' => 'تحسن حالة المريض',
                ],
                [
                    'patient_id' => 14,
                    'nurse_id' => $nurse2->nurse_id,
                    'service_description_ar' => 'رعاية طفل حديث الولادة',
                    'service_description_en' => 'Newborn care',
                    'request_date' => Carbon::now()->toDateString(),
                    'preferred_date' => Carbon::now()->addDays(1)->toDateString(),
                    'preferred_time' => '06:00:00',
                    'duration_hours' => 12,
                    'address_line_1' => 'شارع الأمير محمد بن عبدالعزيز',
                    'address_line_2' => 'حي الزهراء، شقة رقم 15',
                    'city' => 'جدة',
                    'state' => 'منطقة مكة المكرمة',
                    'country' => 'المملكة العربية السعودية',
                    'postal_code' => '21462',
                    'latitude' => 21.5569,
                    'longitude' => 39.1867,
                    'contact_number' => '+966 556667788',
                    'status' => 'pending',
                    'payment_status' => 0,
                    'total_amount' => 2160,
                    'patient_notes' => 'طفل حديث الولادة يحتاج رعاية تمريضية متخصصة على مدار اليوم.',
                    'admin_notes' => null,
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ],
            ];

            foreach ($requests as $requestData) {
                NurseRequest::create($requestData);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
