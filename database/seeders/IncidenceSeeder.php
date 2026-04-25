<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Incidence;
use Carbon\Carbon;

class IncidenceSeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Incidence::truncate();


        if (env('IS_DUMMY_DATA') == false) {

            Schema::enableForeignKeyConstraints();
            return;
        }

        $now = Carbon::now();

        $incidences = [
            // ✅ With Images
            [
                'user_id' => 9,
                'title' => 'عطل في المعدات الطبية',
                'description' => 'جهاز تخطيط القلب لا يعمل بشكل صحيح',
                'phone' => '+966 4578952512',
                'email' => 'john@gmail.com',
                'status' => 1,
                'incident_type' => 1,
                'reply' => 'سيقوم الفني باستبدال جهاز تخطيط القلب غداً',
                'image_path' => public_path('dummy-images/incidence/incidence_1.png'),
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(0),
                'updated_at' => $now->copy()->subDays(0),
                'incident_closed_date' => null,
            ],
            [
                'user_id' => 9,
                'title' => 'تسرب مياه في العناية المركزة',
                'description' => 'تسرب مياه بالقرب من خط إمداد الأكسجين',
                'phone' => '+966 4578952512',
                'email' => 'john@gmail.com',
                'status' => 2,
                'incident_type' => 2,
                'reply' => 'تم حل مشكلة التسرب من قبل الصيانة',
                'image_path' => null,
                'created_by' => 2,
                'updated_by' => 2,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
                'incident_closed_date' => $now->copy()->subDays(0),
            ],
            [
                'user_id' => 9,
                'title' => 'تأخر في خدمات الصيدلية',
                'description' => 'المرضى ينتظرون أكثر من 30 دقيقة',
                'phone' => '+966 4578952512',
                'email' => 'john@gmail.com',
                'status' => 3,
                'incident_type' => 3,
                'reply' => 'نحن بصدد إضافة المزيد من الموظفين',
                'image_path' => public_path('dummy-images/incidence/incidenec_3.jpg'),
                'created_by' => 3,
                'updated_by' => 3,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
                'incident_closed_date' => null,
            ],
            [
                'user_id' => 10,
                'title' => 'نقص في الكراسي المتحركة',
                'description' => 'يوجد كرسيان متحركان فقط',
                'phone' => '+966 7485961545',
                'email' => 'robert@gmail.com',
                'status' => 2,
                'incident_type' => 2,
                'reply' => 'ستصل كراسي جديدة الأسبوع القادم',
                'image_path' => null,
                'created_by' => 5,
                'updated_by' => 5,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now->copy()->subDays(4),
                'incident_closed_date' => $now->copy()->subDays(3),
            ],

            [
                'user_id' => 11,
                'title' => 'عطل في المصعد',
                'description' => 'المصعد بالقرب من الاستقبال لا يعمل',
                'phone' => '+966 2563987448',
                'email' => 'bentley@gmail.com',
                'status' => 1,
                'incident_type' => 1,
                'reply' => '',
                'image_path' => public_path('dummy-images/incidence/incidence_5.jpeg'),
                'created_by' => 6,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
                'incident_closed_date' => null,
            ],
            [
                'user_id' => 12,
                'title' => 'شكوى بخصوص موظفي الأمن',
                'description' => 'لم يتم التعامل مع العائلة بلطف',
                'phone' => '+966 3565478912',
                'email' => 'brian@gmail.com',
                'status' => 3,
                'incident_type' => 3,
                'reply' => '',
                'image_path' => null,
                'created_by' => 7,
                'updated_by' => 7,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(6),
                'updated_at' => $now->copy()->subDays(6),
                'incident_closed_date' => null,
            ],
            [
                'user_id' => 13,
                'title' => 'عدم توفر الصابون في دورات المياه',
                'description' => 'دورات المياه في الطابق الثاني بدون صابون',
                'phone' => '+966 5674587110',
                'email' => 'gilbert@gmail.com',
                'status' => 2,
                'incident_type' => 2,
                'reply' => 'تم إعادة تعبئة الصابون',
                'image_path' => null,
                'created_by' => 8,
                'updated_by' => 8,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(7),
                'incident_closed_date' => $now->copy()->subDays(6),
            ],
            [
                'user_id' => 14,
                'title' => 'التكييف لا يعمل في غرفة الانتظار',
                'description' => 'توقف التكييف في صالة العيادات الخارجية',
                'phone' => '+966 7418529630',
                'email' => 'diana@gmail.com',
                'status' => 1,
                'incident_type' => 1,
                'reply' => 'تم تكليف فني للفحص',
                'image_path' => null,
                'created_by' => 9,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(8),
                'incident_closed_date' => null,
            ],
        ];

        foreach ($incidences as $data) {
            $imagePath = $data['image_path'];
            unset($data['image_path']);

            $incidence = Incidence::create($data);

            if ($imagePath) {
                $this->attachIncidentImage($incidence, $imagePath);
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function attachIncidentImage($model, $publicPath)
    {
        if (!file_exists($publicPath)) return false;

        $file = new \Illuminate\Http\File($publicPath);

        return $model->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('file_url');
    }
}
