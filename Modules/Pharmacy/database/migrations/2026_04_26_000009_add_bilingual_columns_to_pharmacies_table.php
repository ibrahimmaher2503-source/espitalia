<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The core migration 2026_04_11_000005 already owns the bilingual
        // name and description columns. This module migration only adds the
        // legacy description field still consumed by the pharmacy model.
        if (! Schema::hasColumn('pharmacies', 'description')) {
            Schema::table('pharmacies', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name_en');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pharmacies', 'description')) {
            Schema::table('pharmacies', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
