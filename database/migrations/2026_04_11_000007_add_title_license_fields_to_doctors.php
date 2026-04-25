<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctors')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->string('title_ar', 100)->nullable()->after('description_en');
            $table->string('title_en', 100)->nullable()->after('title_ar');
            $table->string('license_number', 50)->nullable()->after('title_en');
            $table->tinyInteger('is_active')->default(1)->after('license_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('doctors')) {
            return;
        }

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en', 'license_number', 'is_active']);
        });
    }
};
