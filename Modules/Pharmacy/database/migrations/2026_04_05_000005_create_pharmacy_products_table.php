<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('sub_category_id');
            $table->index('brand_id');
            $table->index('product_type_id');
            $table->index('is_active');

            // Foreign keys
            $table->foreign('category_id')
                ->references('id')
                ->on('pharmacy_categories')
                ->onDelete('cascade');
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('pharmacy_categories')
                ->onDelete('cascade');
            $table->foreign('brand_id')
                ->references('id')
                ->on('pharmacy_brands')
                ->onDelete('set null');
            $table->foreign('product_type_id')
                ->references('id')
                ->on('pharmacy_product_types')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_products');
    }
};
