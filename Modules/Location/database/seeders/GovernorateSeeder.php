<?php

namespace Modules\Location\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('governorates')->exists()) {
            return;
        }

        $governorates = [
            ['name_ar' => 'القاهرة', 'name_en' => 'Cairo', 'cities' => [
                ['name_ar' => 'مدينة نصر', 'name_en' => 'Nasr City'],
                ['name_ar' => 'المعادي', 'name_en' => 'Maadi'],
                ['name_ar' => 'مصر الجديدة', 'name_en' => 'Heliopolis'],
                ['name_ar' => 'التجمع الخامس', 'name_en' => 'Fifth Settlement'],
                ['name_ar' => 'المقطم', 'name_en' => 'Mokattam'],
                ['name_ar' => 'شبرا', 'name_en' => 'Shubra'],
                ['name_ar' => 'عين شمس', 'name_en' => 'Ain Shams'],
            ]],
            ['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria', 'cities' => [
                ['name_ar' => 'سموحة', 'name_en' => 'Smouha'],
                ['name_ar' => 'المنتزه', 'name_en' => 'Montaza'],
                ['name_ar' => 'سيدي جابر', 'name_en' => 'Sidi Gaber'],
                ['name_ar' => 'العجمي', 'name_en' => 'Agami'],
                ['name_ar' => 'المندرة', 'name_en' => 'Mandara'],
            ]],
            ['name_ar' => 'الجيزة', 'name_en' => 'Giza', 'cities' => [
                ['name_ar' => 'الدقي', 'name_en' => 'Dokki'],
                ['name_ar' => 'المهندسين', 'name_en' => 'Mohandessin'],
                ['name_ar' => 'الهرم', 'name_en' => 'Haram'],
                ['name_ar' => 'فيصل', 'name_en' => 'Faisal'],
                ['name_ar' => 'السادس من أكتوبر', 'name_en' => '6th of October'],
                ['name_ar' => 'الشيخ زايد', 'name_en' => 'Sheikh Zayed'],
            ]],
            ['name_ar' => 'القليوبية', 'name_en' => 'Qalyubia', 'cities' => [
                ['name_ar' => 'بنها', 'name_en' => 'Banha'],
                ['name_ar' => 'شبرا الخيمة', 'name_en' => 'Shubra El Kheima'],
                ['name_ar' => 'القناطر الخيرية', 'name_en' => 'El Qanater El Khairiya'],
            ]],
            ['name_ar' => 'الشرقية', 'name_en' => 'Sharqia', 'cities' => [
                ['name_ar' => 'الزقازيق', 'name_en' => 'Zagazig'],
                ['name_ar' => 'العاشر من رمضان', 'name_en' => '10th of Ramadan'],
                ['name_ar' => 'بلبيس', 'name_en' => 'Bilbeis'],
            ]],
            ['name_ar' => 'الدقهلية', 'name_en' => 'Dakahlia', 'cities' => [
                ['name_ar' => 'المنصورة', 'name_en' => 'Mansoura'],
                ['name_ar' => 'طلخا', 'name_en' => 'Talkha'],
                ['name_ar' => 'ميت غمر', 'name_en' => 'Mit Ghamr'],
            ]],
            ['name_ar' => 'البحيرة', 'name_en' => 'Beheira', 'cities' => [
                ['name_ar' => 'دمنهور', 'name_en' => 'Damanhour'],
                ['name_ar' => 'كفر الدوار', 'name_en' => 'Kafr El Dawwar'],
                ['name_ar' => 'رشيد', 'name_en' => 'Rosetta'],
            ]],
            ['name_ar' => 'المنوفية', 'name_en' => 'Monufia', 'cities' => [
                ['name_ar' => 'شبين الكوم', 'name_en' => 'Shebin El Kom'],
                ['name_ar' => 'منوف', 'name_en' => 'Menouf'],
                ['name_ar' => 'السادات', 'name_en' => 'Sadat City'],
            ]],
            ['name_ar' => 'الغربية', 'name_en' => 'Gharbia', 'cities' => [
                ['name_ar' => 'طنطا', 'name_en' => 'Tanta'],
                ['name_ar' => 'المحلة الكبرى', 'name_en' => 'El Mahalla El Kubra'],
                ['name_ar' => 'كفر الزيات', 'name_en' => 'Kafr El Zayat'],
            ]],
            ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh', 'cities' => [
                ['name_ar' => 'كفر الشيخ', 'name_en' => 'Kafr El Sheikh City'],
                ['name_ar' => 'دسوق', 'name_en' => 'Desouk'],
                ['name_ar' => 'بلطيم', 'name_en' => 'Baltim'],
            ]],
            ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum', 'cities' => [
                ['name_ar' => 'الفيوم', 'name_en' => 'Fayoum City'],
                ['name_ar' => 'إبشواي', 'name_en' => 'Ibsheway'],
                ['name_ar' => 'طامية', 'name_en' => 'Tamiya'],
            ]],
            ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef', 'cities' => [
                ['name_ar' => 'بني سويف', 'name_en' => 'Beni Suef City'],
                ['name_ar' => 'الواسطي', 'name_en' => 'El Wasta'],
            ]],
            ['name_ar' => 'المنيا', 'name_en' => 'Minya', 'cities' => [
                ['name_ar' => 'المنيا', 'name_en' => 'Minya City'],
                ['name_ar' => 'ملوي', 'name_en' => 'Mallawi'],
                ['name_ar' => 'سمالوط', 'name_en' => 'Samalut'],
            ]],
            ['name_ar' => 'أسيوط', 'name_en' => 'Asyut', 'cities' => [
                ['name_ar' => 'أسيوط', 'name_en' => 'Asyut City'],
                ['name_ar' => 'أبنوب', 'name_en' => 'Abnoub'],
                ['name_ar' => 'منفلوط', 'name_en' => 'Manfalut'],
            ]],
            ['name_ar' => 'سوهاج', 'name_en' => 'Sohag', 'cities' => [
                ['name_ar' => 'سوهاج', 'name_en' => 'Sohag City'],
                ['name_ar' => 'أخميم', 'name_en' => 'Akhmim'],
                ['name_ar' => 'جرجا', 'name_en' => 'Girga'],
            ]],
            ['name_ar' => 'قنا', 'name_en' => 'Qena', 'cities' => [
                ['name_ar' => 'قنا', 'name_en' => 'Qena City'],
                ['name_ar' => 'نجع حمادي', 'name_en' => 'Nag Hammadi'],
                ['name_ar' => 'قوص', 'name_en' => 'Qus'],
            ]],
            ['name_ar' => 'الأقصر', 'name_en' => 'Luxor', 'cities' => [
                ['name_ar' => 'الأقصر', 'name_en' => 'Luxor City'],
                ['name_ar' => 'الطود', 'name_en' => 'El Tod'],
                ['name_ar' => 'إسنا', 'name_en' => 'Esna'],
            ]],
            ['name_ar' => 'أسوان', 'name_en' => 'Aswan', 'cities' => [
                ['name_ar' => 'أسوان', 'name_en' => 'Aswan City'],
                ['name_ar' => 'كوم أمبو', 'name_en' => 'Kom Ombo'],
                ['name_ar' => 'إدفو', 'name_en' => 'Edfu'],
            ]],
            ['name_ar' => 'البحر الأحمر', 'name_en' => 'Red Sea', 'cities' => [
                ['name_ar' => 'الغردقة', 'name_en' => 'Hurghada'],
                ['name_ar' => 'سفاجا', 'name_en' => 'Safaga'],
                ['name_ar' => 'مرسى علم', 'name_en' => 'Marsa Alam'],
            ]],
            ['name_ar' => 'الوادي الجديد', 'name_en' => 'New Valley', 'cities' => [
                ['name_ar' => 'الخارجة', 'name_en' => 'Kharga'],
                ['name_ar' => 'الداخلة', 'name_en' => 'Dakhla'],
                ['name_ar' => 'الفرافرة', 'name_en' => 'Farafra'],
            ]],
            ['name_ar' => 'مطروح', 'name_en' => 'Matrouh', 'cities' => [
                ['name_ar' => 'مرسى مطروح', 'name_en' => 'Marsa Matrouh'],
                ['name_ar' => 'سيوة', 'name_en' => 'Siwa'],
                ['name_ar' => 'الحمام', 'name_en' => 'El Hamam'],
            ]],
            ['name_ar' => 'شمال سيناء', 'name_en' => 'North Sinai', 'cities' => [
                ['name_ar' => 'العريش', 'name_en' => 'Arish'],
                ['name_ar' => 'الشيخ زويد', 'name_en' => 'Sheikh Zuweid'],
                ['name_ar' => 'رفح', 'name_en' => 'Rafah'],
            ]],
            ['name_ar' => 'جنوب سيناء', 'name_en' => 'South Sinai', 'cities' => [
                ['name_ar' => 'شرم الشيخ', 'name_en' => 'Sharm El Sheikh'],
                ['name_ar' => 'دهب', 'name_en' => 'Dahab'],
                ['name_ar' => 'طابا', 'name_en' => 'Taba'],
                ['name_ar' => 'نويبع', 'name_en' => 'Nuweiba'],
            ]],
            ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said', 'cities' => [
                ['name_ar' => 'بورسعيد', 'name_en' => 'Port Said City'],
                ['name_ar' => 'بورفؤاد', 'name_en' => 'Port Fouad'],
            ]],
            ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia', 'cities' => [
                ['name_ar' => 'الإسماعيلية', 'name_en' => 'Ismailia City'],
                ['name_ar' => 'فايد', 'name_en' => 'Fayed'],
                ['name_ar' => 'القنطرة شرق', 'name_en' => 'El Qantara East'],
            ]],
            ['name_ar' => 'السويس', 'name_en' => 'Suez', 'cities' => [
                ['name_ar' => 'السويس', 'name_en' => 'Suez City'],
                ['name_ar' => 'عتاقة', 'name_en' => 'Ataka'],
            ]],
            ['name_ar' => 'دمياط', 'name_en' => 'Damietta', 'cities' => [
                ['name_ar' => 'دمياط', 'name_en' => 'Damietta City'],
                ['name_ar' => 'رأس البر', 'name_en' => 'Ras El Bar'],
                ['name_ar' => 'دمياط الجديدة', 'name_en' => 'New Damietta'],
            ]],
        ];

        foreach ($governorates as $govData) {
            $cities = $govData['cities'];
            unset($govData['cities']);

            $govData['created_at'] = now();
            $govData['updated_at'] = now();

            $governorateId = DB::table('governorates')->insertGetId($govData);

            foreach ($cities as $city) {
                $city['governorate_id'] = $governorateId;
                $city['created_at'] = now();
                $city['updated_at'] = now();
                DB::table('governorate_cities')->insert($city);
            }
        }
    }
}
