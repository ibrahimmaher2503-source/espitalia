<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('phone');
            $table->string('phone_secondary', 20)->nullable()->after('email');
            $table->string('tax_number', 50)->nullable()->after('license_number');
            $table->decimal('minimum_order_amount', 10, 2)->default(0)->after('delivery_fee');
            $table->decimal('max_delivery_radius_km', 5, 1)->nullable()->after('minimum_order_amount');
            $table->boolean('is_delivery_enabled')->default(true)->after('is_active');
            $table->boolean('is_pickup_enabled')->default(true)->after('is_delivery_enabled');
            $table->unsignedInteger('avg_preparation_time_min')->default(30)->after('is_pickup_enabled');
            $table->decimal('rating', 2, 1)->default(0)->after('avg_preparation_time_min');
            $table->unsignedInteger('total_orders')->default(0)->after('rating');
            $table->unsignedBigInteger('branch_id')->nullable()->after('vendor_id');

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'email', 'phone_secondary', 'tax_number', 'minimum_order_amount',
                'max_delivery_radius_km', 'is_delivery_enabled', 'is_pickup_enabled',
                'avg_preparation_time_min', 'rating', 'total_orders', 'branch_id',
            ]);
        });
    }
};
