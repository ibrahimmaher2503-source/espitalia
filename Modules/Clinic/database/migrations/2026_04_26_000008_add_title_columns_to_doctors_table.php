<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('description_en');
            }
            if (!Schema::hasColumn('doctors', 'title_en')) {
                $table->string('title_en')->nullable()->after('title_ar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_en']);
        });
    }
};
