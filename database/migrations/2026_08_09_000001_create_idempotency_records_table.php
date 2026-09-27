<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('operation', 64);
            $table->char('key_hash', 64);
            $table->char('request_hash', 64);
            $table->string('request_id', 128)->nullable();
            $table->string('endpoint', 255);
            $table->string('status', 20)->default('processing');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'operation', 'key_hash'], 'idempotency_user_operation_key_unique');
            $table->index(['status', 'expires_at'], 'idempotency_status_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_records');
    }
};
