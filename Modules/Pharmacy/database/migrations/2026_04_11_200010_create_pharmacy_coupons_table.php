<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id')->nullable();
            $table->string('code', 50)->unique();
            $table->string('title_ar', 255);
            $table->string('title_en', 255);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_coupons');
    }
};
