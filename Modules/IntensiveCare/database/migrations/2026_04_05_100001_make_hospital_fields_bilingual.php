<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            // Bilingual name columns
            $table->string('name_ar', 255)->nullable()->after('slug');
            $table->string('name_en', 255)->nullable()->after('name_ar');

            // Bilingual description columns
            $table->text('description_ar')->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description_ar');

            // Bilingual address columns
            $table->string('address_ar', 255)->nullable()->after('description_en');
            $table->string('address_en', 255)->nullable()->after('address_ar');

            // Location FKs
            $table->unsignedBigInteger('governorate_id')->nullable()->after('address_en');
            $table->unsignedBigInteger('city_id')->nullable()->after('governorate_id');

            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');
            $table->index('governorate_id');
            $table->index('city_id');
        });

        // Copy existing data to both language columns
        DB::statement('UPDATE hospitals SET name_ar = name, name_en = name WHERE name IS NOT NULL');
        DB::statement('UPDATE hospitals SET description_ar = description, description_en = description WHERE description IS NOT NULL');
        DB::statement('UPDATE hospitals SET address_ar = address, address_en = address WHERE address IS NOT NULL');

        // Drop old columns
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'address']);
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->string('name')->nullable()->after('slug');
            $table->text('description')->nullable()->after('name');
            $table->string('address')->nullable()->after('description');
        });

        // Copy _en back to restored columns
        DB::statement('UPDATE hospitals SET name = name_en WHERE name_en IS NOT NULL');
        DB::statement('UPDATE hospitals SET description = description_en WHERE description_en IS NOT NULL');
        DB::statement('UPDATE hospitals SET address = address_en WHERE address_en IS NOT NULL');

        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropForeign(['governorate_id']);
            $table->dropForeign(['city_id']);
            $table->dropIndex(['governorate_id']);
            $table->dropIndex(['city_id']);
            $table->dropColumn([
                'name_ar', 'name_en',
                'description_ar', 'description_en',
                'address_ar', 'address_en',
                'governorate_id', 'city_id',
            ]);
        });
    }
};
