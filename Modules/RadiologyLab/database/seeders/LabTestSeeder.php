<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;

class LabTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Get category IDs by slug
            $categoryIds = LabTestCategory::pluck('id', 'slug')->toArray();

            $tests = [
                // Blood Tests (تحاليل الدم)
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل صورة الدم الكاملة', 'en' => 'Complete Blood Count (CBC)'],
                    'slug' => 'tahlil-surat-aldam-alkamila',
                    'code' => 'CBC-001',
                    'description' => ['ar' => 'فحص شامل لمكونات الدم بما في ذلك كريات الدم الحمراء والبيضاء والصفائح الدموية', 'en' => 'Comprehensive blood component test including red blood cells, white blood cells, and platelets'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'لا يتطلب صيام. يفضل إجراء التحليل في الصباح الباكر', 'en' => 'No fasting required. Preferably done in the early morning'],
                    'default_price' => 150.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل وظائف الكبد', 'en' => 'Liver Function Test (LFT)'],
                    'slug' => 'tahlil-wazaif-alkabid',
                    'code' => 'LFT-001',
                    'description' => ['ar' => 'فحص إنزيمات الكبد والبيليروبين والبروتينات لتقييم صحة الكبد', 'en' => 'Test for liver enzymes, bilirubin, and proteins to assess liver health'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'يتطلب صيام لمدة 8-12 ساعة قبل إجراء التحليل', 'en' => 'Requires fasting for 8-12 hours before the test'],
                    'default_price' => 200.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل وظائف الكلى', 'en' => 'Kidney Function Test (KFT)'],
                    'slug' => 'tahlil-wazaif-alkula',
                    'code' => 'KFT-001',
                    'description' => ['ar' => 'قياس مستويات الكرياتينين واليوريا لتقييم وظائف الكلى', 'en' => 'Measure creatinine and urea levels to assess kidney function'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'يفضل الصيام لمدة 8 ساعات. شرب الماء مسموح', 'en' => 'Fasting for 8 hours is preferred. Water is allowed'],
                    'default_price' => 180.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل السكر التراكمي', 'en' => 'HbA1c Test'],
                    'slug' => 'tahlil-alsukar-altarakumi',
                    'code' => 'HBA-001',
                    'description' => ['ar' => 'قياس متوسط مستوى السكر في الدم خلال الثلاثة أشهر الماضية', 'en' => 'Measure average blood sugar level over the past three months'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'لا يتطلب صيام. يمكن إجراؤه في أي وقت', 'en' => 'No fasting required. Can be done at any time'],
                    'default_price' => 120.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل الدهون', 'en' => 'Lipid Profile'],
                    'slug' => 'tahlil-alduhun',
                    'code' => 'LPD-001',
                    'description' => ['ar' => 'قياس مستويات الكوليسترول والدهون الثلاثية في الدم', 'en' => 'Measure cholesterol and triglyceride levels in the blood'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'يتطلب صيام لمدة 12 ساعة قبل إجراء التحليل', 'en' => 'Requires fasting for 12 hours before the test'],
                    'default_price' => 160.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-aldam'] ?? 1,
                    'name' => ['ar' => 'تحليل فيتامين د', 'en' => 'Vitamin D Test'],
                    'slug' => 'tahlil-vitamin-d',
                    'code' => 'VTD-001',
                    'description' => ['ar' => 'قياس مستوى فيتامين د في الدم للكشف عن نقصه', 'en' => 'Measure vitamin D level in blood to detect deficiency'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'لا يتطلب صيام. يفضل إجراؤه في الصباح', 'en' => 'No fasting required. Preferably done in the morning'],
                    'default_price' => 250.00,
                    'turnaround_time' => '48 hours',
                    'status' => 1,
                ],

                // Urine Tests (تحاليل البول)
                [
                    'category_id' => $categoryIds['tahaalil-albawl'] ?? 2,
                    'name' => ['ar' => 'تحليل البول الكامل', 'en' => 'Urinalysis'],
                    'slug' => 'tahlil-albawl-alkamel',
                    'code' => 'UA-001',
                    'description' => ['ar' => 'فحص شامل للبول يشمل الفحص الكيميائي والمجهري', 'en' => 'Comprehensive urine test including chemical and microscopic examination'],
                    'sample_type' => 'urine',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'يفضل جمع عينة البول الصباحية الأولى. تنظيف المنطقة قبل الجمع', 'en' => 'First morning urine sample is preferred. Clean the area before collection'],
                    'default_price' => 80.00,
                    'turnaround_time' => '12 hours',
                    'status' => 1,
                ],

                // X-Ray (الأشعة السينية)
                [
                    'category_id' => $categoryIds['alashiaa-alsiniia'] ?? 3,
                    'name' => ['ar' => 'أشعة سينية للصدر', 'en' => 'Chest X-Ray'],
                    'slug' => 'ashiaa-siniia-lilsadr',
                    'code' => 'XR-001',
                    'description' => ['ar' => 'تصوير بالأشعة السينية للصدر للكشف عن أمراض الرئة والقلب', 'en' => 'Chest X-ray imaging to detect lung and heart diseases'],
                    'sample_type' => 'imaging',
                    'department' => 'radiology',
                    'preparation_instructions' => ['ar' => 'إزالة المجوهرات والأجسام المعدنية. ارتداء ملابس المستشفى', 'en' => 'Remove jewelry and metal objects. Wear hospital gown'],
                    'default_price' => 300.00,
                    'turnaround_time' => '4 hours',
                    'status' => 1,
                ],

                // MRI (الرنين المغناطيسي)
                [
                    'category_id' => $categoryIds['alranin-almighnatisi'] ?? 4,
                    'name' => ['ar' => 'رنين مغناطيسي للدماغ', 'en' => 'Brain MRI'],
                    'slug' => 'ranin-mighnatisi-lildimagh',
                    'code' => 'MRI-001',
                    'description' => ['ar' => 'تصوير بالرنين المغناطيسي للدماغ للكشف عن الأورام والسكتات الدماغية', 'en' => 'Brain MRI to detect tumors and strokes'],
                    'sample_type' => 'imaging',
                    'department' => 'radiology',
                    'preparation_instructions' => ['ar' => 'إزالة جميع الأجسام المعدنية. إبلاغ الطبيب عن أي غرسات معدنية. الصيام لمدة 4 ساعات', 'en' => 'Remove all metal objects. Inform doctor about any metal implants. Fast for 4 hours'],
                    'default_price' => 1500.00,
                    'turnaround_time' => '48 hours',
                    'status' => 1,
                ],

                // Ultrasound (الموجات فوق الصوتية)
                [
                    'category_id' => $categoryIds['almawjat-fawq-alsawtia'] ?? 5,
                    'name' => ['ar' => 'موجات صوتية للبطن', 'en' => 'Abdominal Ultrasound'],
                    'slug' => 'mawjat-sawtia-lilbatn',
                    'code' => 'US-001',
                    'description' => ['ar' => 'فحص بالموجات فوق الصوتية لأعضاء البطن بما في ذلك الكبد والكلى والمرارة', 'en' => 'Ultrasound examination of abdominal organs including liver, kidneys, and gallbladder'],
                    'sample_type' => 'imaging',
                    'department' => 'radiology',
                    'preparation_instructions' => ['ar' => 'الصيام لمدة 6-8 ساعات قبل الفحص. شرب الماء بكثرة قبل ساعة من الفحص', 'en' => 'Fast for 6-8 hours before the exam. Drink plenty of water one hour before'],
                    'default_price' => 400.00,
                    'turnaround_time' => '4 hours',
                    'status' => 1,
                ],

                // CT Scan (التصوير المقطعي)
                [
                    'category_id' => $categoryIds['altaswir-almaqtaei'] ?? 6,
                    'name' => ['ar' => 'أشعة مقطعية للصدر', 'en' => 'Chest CT Scan'],
                    'slug' => 'ashiaa-maqtaiia-lilsadr',
                    'code' => 'CT-001',
                    'description' => ['ar' => 'تصوير مقطعي محوسب للصدر لتشخيص الأورام وأمراض الرئة', 'en' => 'Computed tomography of chest for diagnosing tumors and lung diseases'],
                    'sample_type' => 'imaging',
                    'department' => 'radiology',
                    'preparation_instructions' => ['ar' => 'الصيام لمدة 4 ساعات. إبلاغ الطبيب عن أي حساسية من الصبغات', 'en' => 'Fast for 4 hours. Inform doctor about any dye allergies'],
                    'default_price' => 1200.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],

                // Hormone Tests (تحاليل الهرمونات)
                [
                    'category_id' => $categoryIds['tahaalil-alhormonat'] ?? 7,
                    'name' => ['ar' => 'تحليل الغدة الدرقية', 'en' => 'Thyroid Function Test'],
                    'slug' => 'tahlil-alghudda-aldaraqia',
                    'code' => 'THY-001',
                    'description' => ['ar' => 'قياس مستويات هرمونات الغدة الدرقية T3 و T4 و TSH', 'en' => 'Measure thyroid hormone levels T3, T4, and TSH'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'يفضل إجراء التحليل صباحاً قبل تناول أدوية الغدة الدرقية', 'en' => 'Preferably done in the morning before taking thyroid medication'],
                    'default_price' => 280.00,
                    'turnaround_time' => '24 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-alhormonat'] ?? 7,
                    'name' => ['ar' => 'تحليل هرمون الحمل', 'en' => 'Pregnancy Test (HCG)'],
                    'slug' => 'tahlil-hurmon-alhaml',
                    'code' => 'HCG-001',
                    'description' => ['ar' => 'قياس مستوى هرمون الحمل في الدم للكشف المبكر عن الحمل', 'en' => 'Measure pregnancy hormone level in blood for early pregnancy detection'],
                    'sample_type' => 'blood',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'لا يتطلب صيام. يفضل إجراؤه صباحاً للحصول على نتائج أدق', 'en' => 'No fasting required. Preferably done in the morning for more accurate results'],
                    'default_price' => 100.00,
                    'turnaround_time' => '6 hours',
                    'status' => 1,
                ],

                // Tissue Tests (تحاليل الأنسجة)
                [
                    'category_id' => $categoryIds['tahaalil-alansija'] ?? 8,
                    'name' => ['ar' => 'مسحة عنق الرحم', 'en' => 'Pap Smear'],
                    'slug' => 'masha-unuq-alrahim',
                    'code' => 'PAP-001',
                    'description' => ['ar' => 'فحص خلايا عنق الرحم للكشف المبكر عن سرطان عنق الرحم', 'en' => 'Cervical cell examination for early detection of cervical cancer'],
                    'sample_type' => 'swab',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'تجنب الغسول المهبلي والعلاقة الحميمة قبل 48 ساعة من الفحص', 'en' => 'Avoid vaginal douche and intercourse 48 hours before the test'],
                    'default_price' => 350.00,
                    'turnaround_time' => '72 hours',
                    'status' => 1,
                ],
                [
                    'category_id' => $categoryIds['tahaalil-alansija'] ?? 8,
                    'name' => ['ar' => 'خزعة نسيجية', 'en' => 'Tissue Biopsy'],
                    'slug' => 'khuzaa-nasijia',
                    'code' => 'BIO-001',
                    'description' => ['ar' => 'أخذ عينة من الأنسجة لفحصها تحت المجهر للكشف عن الأورام والأمراض', 'en' => 'Taking a tissue sample for microscopic examination to detect tumors and diseases'],
                    'sample_type' => 'tissue',
                    'department' => 'laboratory',
                    'preparation_instructions' => ['ar' => 'استشارة الطبيب حول الأدوية المسيلة للدم. الصيام حسب تعليمات الطبيب', 'en' => 'Consult doctor about blood thinners. Fast according to doctor instructions'],
                    'default_price' => 800.00,
                    'turnaround_time' => '5-7 days',
                    'status' => 1,
                ],
            ];

            foreach ($tests as $test) {
                $record = LabTest::updateOrCreate(
                    ['code' => $test['code']],
                    $test
                );
                $record->slug = $test['slug'];
                $record->saveQuietly();
            }

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
