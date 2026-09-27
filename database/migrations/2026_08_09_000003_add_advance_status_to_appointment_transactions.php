<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointment_transactions') || Schema::hasColumn('appointment_transactions', 'advance_payment_status')) {
            return;
        }

        Schema::table('appointment_transactions', function (Blueprint $table) {
            $table->boolean('advance_payment_status')->default(false)->after('payment_status');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('appointment_transactions') && Schema::hasColumn('appointment_transactions', 'advance_payment_status')) {
            Schema::table('appointment_transactions', function (Blueprint $table) {
                $table->dropColumn('advance_payment_status');
            });
        }
    }
};
