<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 255);
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('product_id');

            // Foreign keys
            $table->foreign('order_id')
                ->references('id')
                ->on('pharmacy_orders')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('pharmacy_products')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_order_items');
    }
};
