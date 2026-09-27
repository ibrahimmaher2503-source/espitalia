<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pharmacy_orders') && ! Schema::hasColumn('pharmacy_orders', 'delivery_address')) {
            Schema::table('pharmacy_orders', function (Blueprint $table) {
                $table->text('delivery_address')->nullable()->after('address_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pharmacy_orders') && Schema::hasColumn('pharmacy_orders', 'delivery_address')) {
            Schema::table('pharmacy_orders', function (Blueprint $table) {
                $table->dropColumn('delivery_address');
            });
        }
    }
};
