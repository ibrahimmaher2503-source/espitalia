<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add available_nurses and lab_tests keys to the header menu setting.
     */
    public function up(): void
    {
        if (!Schema::hasTable('frontend_settings')) {
            return;
        }

        $setting = DB::table('frontend_settings')
            ->where('key', 'heder-menu-setting')
            ->first();

        if ($setting) {
            $value = json_decode($setting->value, true);

            if (!isset($value['available_nurses'])) {
                $value['available_nurses'] = 1;
            }

            if (!isset($value['lab_tests'])) {
                $value['lab_tests'] = 1;
            }

            if (!isset($value['icu_hospitals'])) {
                $value['icu_hospitals'] = 1;
            }

            DB::table('frontend_settings')
                ->where('id', $setting->id)
                ->update(['value' => json_encode($value)]);
        }
    }

    /**
     * Remove available_nurses and lab_tests keys from the header menu setting.
     */
    public function down(): void
    {
        if (!Schema::hasTable('frontend_settings')) {
            return;
        }

        $setting = DB::table('frontend_settings')
            ->where('key', 'heder-menu-setting')
            ->first();

        if ($setting) {
            $value = json_decode($setting->value, true);

            unset($value['available_nurses'], $value['lab_tests']);

            DB::table('frontend_settings')
                ->where('id', $setting->id)
                ->update(['value' => json_encode($value)]);
        }
    }
};
