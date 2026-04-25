<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('title_ar');
            $table->string('title_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('short_label_ar', 100)->nullable();
            $table->string('short_label_en', 100)->nullable();
            $table->text('terms_ar')->nullable();
            $table->text('terms_en')->nullable();
            $table->string('banner_text_ar')->nullable();
            $table->string('banner_text_en')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('internal_name')->nullable();

            // Discount Configuration
            $table->string('discount_type', 30);
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->decimal('minimum_order_amount', 12, 2)->default(0);
            $table->decimal('override_price', 12, 2)->nullable();
            $table->unsignedInteger('buy_quantity')->nullable();
            $table->unsignedInteger('get_quantity')->nullable();
            $table->unsignedBigInteger('get_service_id')->nullable();
            $table->decimal('get_discount_percentage', 5, 2)->nullable();

            // Coupon
            $table->string('code', 50)->nullable()->unique();
            $table->boolean('requires_coupon')->default(false);
            $table->boolean('auto_apply')->default(false);

            // Targeting Scope
            $table->string('target_scope', 30)->default('global');
            $table->boolean('applies_to_all_services')->default(true);
            $table->boolean('applies_to_all_branches')->default(true);
            $table->boolean('applies_to_all_categories')->default(true);
            $table->unsignedBigInteger('vendor_id')->nullable();

            // Customer Eligibility
            $table->string('customer_scope', 30)->default('all');
            $table->boolean('first_booking_only')->default(false);
            $table->unsignedInteger('min_completed_bookings')->default(0);
            $table->unsignedInteger('max_completed_bookings')->nullable();

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_flash')->default(false);

            // Usage Limits
            $table->unsignedInteger('usage_limit_total')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);

            // Priority & Stacking
            $table->integer('priority')->default(0);
            $table->boolean('stackable')->default(false);
            $table->string('exclusive_group', 50)->nullable();

            // Display
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft');

            // Booking Channel
            $table->json('allowed_channels')->nullable();
            $table->json('allowed_payment_methods')->nullable();

            // Commission
            $table->boolean('commission_on_original')->default(true);

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status', 'idx_offers_status');
            $table->index('is_active', 'idx_offers_active');
            $table->index('vendor_id', 'idx_offers_vendor');
            $table->index(['starts_at', 'ends_at'], 'idx_offers_dates');
            $table->index('priority', 'idx_offers_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
