<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one inventory per product per pharmacy
            $table->unique(['pharmacy_id', 'product_id']);

            // Indexes
            $table->index('pharmacy_id');
            $table->index('product_id');
            $table->index(['product_id', 'stock_qty', 'is_available']);

            // Foreign keys
            $table->foreign('pharmacy_id')
                ->references('id')
                ->on('pharmacies')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('pharmacy_products')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_inventory');
    }
};
