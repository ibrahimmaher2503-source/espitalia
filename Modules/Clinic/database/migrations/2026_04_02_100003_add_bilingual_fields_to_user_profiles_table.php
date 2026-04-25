<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('expert_ar', 255)->nullable()->after('expert');
            $table->string('expert_en', 255)->nullable()->after('expert_ar');
            $table->text('about_self_ar')->nullable()->after('about_self');
            $table->text('about_self_en')->nullable()->after('about_self_ar');
        });

        // Copy existing data to bilingual columns
        DB::table('user_profiles')->whereNotNull('expert')->update([
            'expert_en' => DB::raw('expert'),
            'expert_ar' => DB::raw('expert'),
        ]);

        DB::table('user_profiles')->whereNotNull('about_self')->update([
            'about_self_en' => DB::raw('about_self'),
            'about_self_ar' => DB::raw('about_self'),
        ]);
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['expert_ar', 'expert_en', 'about_self_ar', 'about_self_en']);
        });
    }
};
