<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type', 50);
            $table->string('title_ar', 255);
            $table->string('title_en', 255);
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('type');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_notifications');
    }
};
