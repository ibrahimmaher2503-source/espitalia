<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('offer_id', 'idx_audit_offer');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_audit_logs');
    }
};
