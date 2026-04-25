<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamp('created_at')->nullable();

            $table->unique(['offer_id', 'category_id'], 'uq_offer_category');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_categories');
    }
};
