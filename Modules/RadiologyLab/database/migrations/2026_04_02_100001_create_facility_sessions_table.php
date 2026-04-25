<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_sessions', function (Blueprint $table) {
            $table->id();
            $table->enum('facility_type', ['lab', 'radiology']);
            $table->unsignedBigInteger('facility_id');
            $table->string('day', 20);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration')->default(30);
            $table->json('breaks')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('status')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['facility_type', 'facility_id', 'day']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_sessions');
    }
};
