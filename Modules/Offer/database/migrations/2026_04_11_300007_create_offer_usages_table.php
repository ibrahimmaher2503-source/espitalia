<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('booking_type', 50);
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('final_amount', 12, 2);
            $table->string('coupon_code', 50)->nullable();
            $table->string('status', 20)->default('applied');
            $table->timestamp('reversed_at')->nullable();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();

            $table->index('offer_id', 'idx_usage_offer');
            $table->index('user_id', 'idx_usage_user');
            $table->index(['booking_type', 'booking_id'], 'idx_usage_booking');
            $table->index('status', 'idx_usage_status');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_usages');
    }
};
