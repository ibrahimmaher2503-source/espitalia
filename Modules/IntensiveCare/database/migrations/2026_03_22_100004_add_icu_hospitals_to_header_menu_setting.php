<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add icu_hospitals key to the header menu setting.
     */
    public function up(): void
    {
        if (! Schema::hasTable('frontend_settings')) {
            return;
        }

        $setting = DB::table('frontend_settings')
            ->where('key', 'heder-menu-setting')
            ->first();

        if ($setting) {
            $value = json_decode($setting->value, true);

            if (! isset($value['icu_hospitals'])) {
                $value['icu_hospitals'] = 1;
            }

            DB::table('frontend_settings')
                ->where('id', $setting->id)
                ->update(['value' => json_encode($value)]);
        }
    }

    /**
     * Remove icu_hospitals key from the header menu setting.
     */
    public function down(): void
    {
        if (! Schema::hasTable('frontend_settings')) {
            return;
        }

        $setting = DB::table('frontend_settings')
            ->where('key', 'heder-menu-setting')
            ->first();

        if ($setting) {
            $value = json_decode($setting->value, true);

            unset($value['icu_hospitals']);

            DB::table('frontend_settings')
                ->where('id', $setting->id)
                ->update(['value' => json_encode($value)]);
        }
    }
};
