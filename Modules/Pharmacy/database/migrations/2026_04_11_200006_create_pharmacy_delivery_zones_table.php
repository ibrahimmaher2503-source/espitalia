<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->string('name_ar', 255);
            $table->string('name_en', 255);
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->unsignedInteger('estimated_delivery_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('governorate_cities')->onDelete('set null');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_delivery_zones');
    }
};
