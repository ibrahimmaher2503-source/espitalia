<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointments')) {
            return;
        }

        if (! Schema::hasColumn('appointments', 'booking_slot_key')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('booking_slot_key', 150)->nullable()->after('appointment_time');
            });
        }

        // Preserve all legacy rows. If old data contains duplicate active
        // slots, leave later rows unkeyed rather than deleting data; all new
        // critical API bookings are protected by the unique key below.
        $seen = [];
        DB::table('appointments')
            ->whereNull('deleted_at')
            ->whereNotNull('doctor_id')
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->orderBy('id')
            ->get(['id', 'doctor_id', 'appointment_date', 'appointment_time'])
            ->each(function ($appointment) use (&$seen): void {
                $date = date('Y-m-d', strtotime((string) $appointment->appointment_date));
                $time = date('H:i:s', strtotime((string) $appointment->appointment_time));
                $key = implode('|', [(int) $appointment->doctor_id, $date, $time]);

                if (isset($seen[$key])) {
                    return;
                }

                $seen[$key] = true;
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['booking_slot_key' => $key]);
            });

        Schema::table('appointments', function (Blueprint $table) {
            $table->unique('booking_slot_key', 'appointments_booking_slot_key_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('appointments') || ! Schema::hasColumn('appointments', 'booking_slot_key')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_booking_slot_key_unique');
            $table->dropColumn('booking_slot_key');
        });
    }
};
