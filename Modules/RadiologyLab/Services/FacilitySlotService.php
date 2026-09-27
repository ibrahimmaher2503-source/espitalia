<?php

namespace Modules\RadiologyLab\Services;

use Carbon\Carbon;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Models\FacilitySession;

class FacilitySlotService
{
    /**
     * Get available time slots for a facility on a given date.
     *
     * @param  string  $facilityType  'lab' or 'radiology'
     * @param  int  $facilityId
     * @param  string  $date  Y-m-d format
     * @return array Array of {time: 'H:i', available: bool}
     */
    public function getAvailableSlots(string $facilityType, int $facilityId, string $date): array
    {
        $carbon = Carbon::parse($date);
        $dayOfWeek = strtolower($carbon->locale('en')->dayName);

        $session = FacilitySession::forFacility($facilityType, $facilityId)
            ->forDay($dayOfWeek)
            ->active()
            ->first();

        if (! $session || $session->is_holiday) {
            return [];
        }

        $timezone = new \DateTimeZone(setting('default_time_zone') ?? 'UTC');
        $startTime = Carbon::parse($session->start_time, $timezone);
        $endTime = Carbon::parse($session->end_time, $timezone);
        $slotDuration = $session->slot_duration;
        $breaks = $session->breaks ?? [];

        // Generate all slot start times
        $slots = [];
        $current = $startTime->copy();
        while ($current < $endTime) {
            $inBreak = false;
            foreach ($breaks as $break) {
                $breakStart = Carbon::parse($break['start_break'] ?? $break['start'] ?? '00:00', $timezone);
                $breakEnd = Carbon::parse($break['end_break'] ?? $break['end'] ?? '00:00', $timezone);
                if ($current >= $breakStart && $current < $breakEnd) {
                    $inBreak = true;
                    break;
                }
            }
            if (! $inBreak) {
                $slots[] = $current->format('H:i');
            }
            $current->addMinutes($slotDuration);
        }

        // Get booked times for this facility+date (exclude cancelled)
        $bookedTimes = FacilityBooking::where('type', $facilityType)
            ->where($facilityType === 'lab' ? 'lab_id' : 'radiology_center_id', $facilityId)
            ->whereDate('booking_date', $carbon->toDateString())
            ->where('status', '!=', FacilityBooking::STATUS_CANCELLED)
            ->pluck('booking_time')
            ->map(fn ($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        // Exclude past slots when booking for today
        $now = Carbon::now($timezone);
        $isToday = $carbon->isToday();

        return array_values(array_map(function ($time) use ($bookedTimes, $isToday, $now, $timezone) {
            $isPast = $isToday && Carbon::parse($time, $timezone)->lte($now);

            return [
                'time' => $time,
                'available' => ! $isPast && ! in_array($time, $bookedTimes),
            ];
        }, $slots));
    }
}
