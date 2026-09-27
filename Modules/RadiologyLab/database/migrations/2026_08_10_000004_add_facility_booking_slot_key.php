<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('facility_bookings', 'slot_key')) {
            Schema::table('facility_bookings', function (Blueprint $table) {
                $table->string('slot_key', 200)->nullable()->after('booking_time');
            });
        }

        $seen = [];
        DB::table('facility_bookings')
            ->select(['id', 'type', 'lab_id', 'radiology_center_id', 'booking_date', 'booking_time', 'status'])
            ->orderBy('id')
            ->get()
            ->each(function ($booking) use (&$seen): void {
                if ($booking->status === 'cancelled') {
                    return;
                }

                $facilityId = $booking->type === 'lab'
                    ? $booking->lab_id
                    : $booking->radiology_center_id;
                $date = date('Y-m-d', strtotime((string) $booking->booking_date));
                $time = date('H:i:s', strtotime((string) $booking->booking_time));
                $slotKey = implode('|', [
                    $booking->type,
                    (int) $facilityId,
                    $date,
                    $time,
                ]);

                // Preserve all rows while allowing the unique constraint to
                // protect new writes. Existing duplicate data remains
                // visible for operational reconciliation instead of being
                // silently deleted during deployment.
                if (isset($seen[$slotKey])) {
                    return;
                }

                $seen[$slotKey] = true;
                DB::table('facility_bookings')
                    ->where('id', $booking->id)
                    ->update(['slot_key' => $slotKey]);
            });

        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->unique('slot_key', 'facility_bookings_slot_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->dropUnique('facility_bookings_slot_key_unique');
            $table->dropColumn('slot_key');
        });
    }
};
