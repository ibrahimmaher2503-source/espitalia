<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_orders', function (Blueprint $table) {
            // Delivery/Pickup
            $table->string('delivery_type', 20)->default('delivery')->after('status');
            $table->text('delivery_notes')->nullable()->after('delivery_type');
            $table->timestamp('estimated_delivery_at')->nullable()->after('delivery_notes');
            $table->timestamp('actual_delivery_at')->nullable()->after('estimated_delivery_at');
            $table->unsignedBigInteger('delivery_person_id')->nullable()->after('actual_delivery_at');
            $table->string('pickup_code', 10)->nullable()->after('delivery_person_id');
            $table->timestamp('pickup_verified_at')->nullable()->after('pickup_code');

            // Coupon
            $table->unsignedBigInteger('coupon_id')->nullable()->after('payment_method');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_id');

            // Cancellation
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('total');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');

            // Refund
            $table->string('refund_status', 20)->nullable()->after('cancelled_at');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('refund_status');

            // Foreign keys
            $table->foreign('delivery_person_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('coupon_id')->references('id')->on('pharmacy_coupons')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_person_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'delivery_type', 'delivery_notes', 'estimated_delivery_at', 'actual_delivery_at',
                'delivery_person_id', 'pickup_code', 'pickup_verified_at',
                'coupon_id', 'coupon_discount',
                'cancelled_by', 'cancellation_reason', 'cancelled_at',
                'refund_status', 'refund_amount',
            ]);
        });
    }
};
