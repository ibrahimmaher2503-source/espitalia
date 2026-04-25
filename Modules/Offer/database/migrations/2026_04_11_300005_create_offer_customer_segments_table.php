<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_customer_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->string('segment_type', 30);
            $table->string('segment_value', 100);
            $table->timestamp('created_at')->nullable();

            $table->unique(['offer_id', 'segment_type', 'segment_value'], 'uq_offer_segment');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_customer_segments');
    }
};
