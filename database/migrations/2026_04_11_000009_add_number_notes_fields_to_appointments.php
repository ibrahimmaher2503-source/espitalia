<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'appointment_number')) {
                $table->string('appointment_number', 20)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('appointments', 'notes_ar')) {
                $table->text('notes_ar')->nullable()->after('advance_paid_amount');
            }
            if (!Schema::hasColumn('appointments', 'notes_en')) {
                $table->text('notes_en')->nullable()->after('advance_paid_amount');
            }
            if (!Schema::hasColumn('appointments', 'cancellation_charge_amount')) {
                $table->decimal('cancellation_charge_amount', 10, 2)->default(0)->after('advance_paid_amount');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $columns = ['appointment_number', 'notes_ar', 'notes_en', 'cancellation_charge_amount'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('appointments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
