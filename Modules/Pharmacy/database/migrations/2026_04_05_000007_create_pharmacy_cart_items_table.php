<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            // Unique constraint: one cart item per product per user
            $table->unique(['user_id', 'product_id']);

            // Indexes
            $table->index('user_id');

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('product_id')
                ->references('id')
                ->on('pharmacy_products')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_cart_items');
    }
};
