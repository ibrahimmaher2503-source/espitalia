<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('experience_ar', 255)->nullable()->after('experience');
            $table->string('experience_en', 255)->nullable()->after('experience_ar');
            $table->string('description_ar', 255)->nullable()->after('Description');
            $table->string('description_en', 255)->nullable()->after('description_ar');
        });

        // Copy existing data to bilingual columns
        DB::table('doctors')->whereNotNull('experience')->update([
            'experience_en' => DB::raw('experience'),
            'experience_ar' => DB::raw('experience'),
        ]);

        DB::table('doctors')->whereNotNull('Description')->update([
            'description_en' => DB::raw('`Description`'),
            'description_ar' => DB::raw('`Description`'),
        ]);
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['experience_ar', 'experience_en', 'description_ar', 'description_en']);
        });
    }
};
