<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('call_type', ['video', 'phone'])->nullable()->after('booking_type');
            $table->string('meeting_link', 500)->nullable()->after('call_type');
            $table->unsignedBigInteger('call_service_id')->nullable()->after('independent_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['call_type', 'meeting_link', 'call_service_id']);
        });
    }
};
