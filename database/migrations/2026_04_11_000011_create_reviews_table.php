<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('rating', 2, 1);
            $table->string('title')->nullable();
            $table->text('review_msg_ar')->nullable();
            $table->text('review_msg_en')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->tinyInteger('is_approved')->default(1);
            $table->tinyInteger('is_anonymous')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reviewable_type', 'reviewable_id']);
            $table->index('user_id');
            $table->index('rating');
            $table->index('is_approved');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
