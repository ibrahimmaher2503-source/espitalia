<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refunds: frequently queried by order_id and user_id
        Schema::table('pharmacy_refunds', function (Blueprint $table) {
            $table->index('order_id', 'idx_refunds_order');
            $table->index('user_id', 'idx_refunds_user');
        });

        // Coupons: frequently queried by pharmacy_id
        Schema::table('pharmacy_coupons', function (Blueprint $table) {
            $table->index('pharmacy_id', 'idx_coupons_pharmacy');
        });

        // Delivery zones: compound index for zone-based fee lookup
        Schema::table('pharmacy_delivery_zones', function (Blueprint $table) {
            $table->index(['pharmacy_id', 'governorate_id', 'city_id'], 'idx_zones_pharmacy_location');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_refunds', function (Blueprint $table) {
            $table->dropIndex('idx_refunds_order');
            $table->dropIndex('idx_refunds_user');
        });

        Schema::table('pharmacy_coupons', function (Blueprint $table) {
            $table->dropIndex('idx_coupons_pharmacy');
        });

        Schema::table('pharmacy_delivery_zones', function (Blueprint $table) {
            $table->dropIndex('idx_zones_pharmacy_location');
        });
    }
};
