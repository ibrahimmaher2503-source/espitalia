<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_visit_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visit_request_id')->comment('FK to doctor_visit_requests.id');
            $table->string('old_status')->nullable()->comment('Status before transition (null for initial creation)');
            $table->string('new_status')->comment('Status after transition');
            $table->unsignedBigInteger('changed_by')->comment('FK to users.id');
            $table->text('note')->nullable()->comment('Optional context/reason for the change');
            $table->timestamps();

            $table->foreign('visit_request_id')->references('id')->on('doctor_visit_requests')->onDelete('cascade');
            $table->index('visit_request_id');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_visit_status_histories');
    }
};
