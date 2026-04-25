<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\RadiologyLab\Models\LabTestCategory;

class LabTestCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $categories = [
                [
                    'name' => ['ar' => 'تحاليل الدم', 'en' => 'Blood Tests'],
                    'slug' => 'tahaalil-aldam',
                    'description' => ['ar' => 'جميع تحاليل الدم الشاملة بما في ذلك صورة الدم الكاملة ووظائف الأعضاء', 'en' => 'All comprehensive blood tests including CBC and organ function tests'],
                    'icon' => 'ph ph-drop',
                    'display_order' => 1,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'تحاليل البول', 'en' => 'Urine Tests'],
                    'slug' => 'tahaalil-albawl',
                    'description' => ['ar' => 'تحاليل البول الكاملة للكشف عن الأمراض والالتهابات', 'en' => 'Complete urine tests for detecting diseases and infections'],
                    'icon' => 'ph ph-flask',
                    'display_order' => 2,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'الأشعة السينية', 'en' => 'X-Ray'],
                    'slug' => 'alashiaa-alsiniia',
                    'description' => ['ar' => 'تصوير بالأشعة السينية لتشخيص الكسور وأمراض الصدر', 'en' => 'X-ray imaging for diagnosing fractures and chest diseases'],
                    'icon' => 'ph ph-x-ray',
                    'display_order' => 3,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'الرنين المغناطيسي', 'en' => 'MRI'],
                    'slug' => 'alranin-almighnatisi',
                    'description' => ['ar' => 'تصوير بالرنين المغناطيسي للأعضاء والأنسجة الرخوة', 'en' => 'Magnetic resonance imaging for organs and soft tissues'],
                    'icon' => 'ph ph-atom',
                    'display_order' => 4,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'الموجات فوق الصوتية', 'en' => 'Ultrasound'],
                    'slug' => 'almawjat-fawq-alsawtia',
                    'description' => ['ar' => 'فحص بالموجات فوق الصوتية للأعضاء الداخلية والحمل', 'en' => 'Ultrasound examination for internal organs and pregnancy'],
                    'icon' => 'ph ph-wave-sine',
                    'display_order' => 5,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'التصوير المقطعي', 'en' => 'CT Scan'],
                    'slug' => 'altaswir-almaqtaei',
                    'description' => ['ar' => 'تصوير مقطعي محوسب لتشخيص الأورام والأمراض الداخلية', 'en' => 'Computed tomography for diagnosing tumors and internal diseases'],
                    'icon' => 'ph ph-scan',
                    'display_order' => 6,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'تحاليل الهرمونات', 'en' => 'Hormone Tests'],
                    'slug' => 'tahaalil-alhormonat',
                    'description' => ['ar' => 'فحوصات الهرمونات بما في ذلك الغدة الدرقية وهرمونات الخصوبة', 'en' => 'Hormone tests including thyroid and fertility hormones'],
                    'icon' => 'ph ph-heartbeat',
                    'display_order' => 7,
                    'status' => 1,
                ],
                [
                    'name' => ['ar' => 'تحاليل الأنسجة', 'en' => 'Tissue Tests'],
                    'slug' => 'tahaalil-alansija',
                    'description' => ['ar' => 'فحوصات الأنسجة والخزعات للكشف عن الأورام والأمراض', 'en' => 'Tissue tests and biopsies for detecting tumors and diseases'],
                    'icon' => 'ph ph-microscope',
                    'display_order' => 8,
                    'status' => 1,
                ],
            ];

            foreach ($categories as $category) {
                $record = LabTestCategory::updateOrCreate(
                    ['slug' => $category['slug']],
                    $category
                );
                $record->slug = $category['slug'];
                $record->saveQuietly();
            }

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
