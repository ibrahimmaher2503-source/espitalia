<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamp('created_at')->nullable();

            $table->foreign('coupon_id')->references('id')->on('pharmacy_coupons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('order_id')->references('id')->on('pharmacy_orders');
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_coupon_usages');
    }
};
