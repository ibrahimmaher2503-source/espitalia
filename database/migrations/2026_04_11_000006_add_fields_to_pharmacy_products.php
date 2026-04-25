<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pharmacy_products')) {
            return;
        }

        Schema::table('pharmacy_products', function (Blueprint $table) {
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');
            $table->string('barcode', 50)->nullable()->after('is_active');
            $table->string('sku', 50)->nullable()->after('barcode');
            $table->decimal('default_price', 10, 2)->nullable()->after('sku');
            $table->string('dosage_form', 50)->nullable()->after('default_price');
            $table->string('strength', 100)->nullable()->after('dosage_form');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pharmacy_products')) {
            return;
        }

        Schema::table('pharmacy_products', function (Blueprint $table) {
            $table->dropColumn(['description_ar', 'description_en', 'barcode', 'sku', 'default_price', 'dosage_form', 'strength']);
        });
    }
};
