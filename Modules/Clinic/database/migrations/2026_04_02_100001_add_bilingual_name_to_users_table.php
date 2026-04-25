<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name_ar', 255)->nullable()->after('first_name');
            $table->string('first_name_en', 255)->nullable()->after('first_name_ar');
            $table->string('last_name_ar', 255)->nullable()->after('last_name');
            $table->string('last_name_en', 255)->nullable()->after('last_name_ar');
        });

        // Copy existing data to bilingual columns
        DB::table('users')->whereNotNull('first_name')->update([
            'first_name_en' => DB::raw('first_name'),
            'first_name_ar' => DB::raw('first_name'),
        ]);

        DB::table('users')->whereNotNull('last_name')->update([
            'last_name_en' => DB::raw('last_name'),
            'last_name_ar' => DB::raw('last_name'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name_ar', 'first_name_en', 'last_name_ar', 'last_name_en']);
        });
    }
};
