<?php

namespace Modules\RadiologyLab\database\seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;

class FacilityBookingSeeder extends Seeder
{
    public function run(): void
    {
        if (env('IS_DUMMY_DATA')) {
            $patients = User::role('user')->take(5)->get();
            if ($patients->isEmpty()) {
                $patients = User::take(5)->get();
            }

            $labs = Lab::where('status', 1)->get();
            $centers = RadiologyCenter::where('status', 1)->get();

            if ($patients->isEmpty() || ($labs->isEmpty() && $centers->isEmpty())) {
                return;
            }

            $admin = User::role('admin')->first();
            $adminId = $admin->id ?? 1;

            $bookings = [
                // Lab bookings
                [
                    'type' => 'lab',
                    'lab_id' => $labs[0]->id ?? null,
                    'radiology_center_id' => null,
                    'patient_id' => $patients[0]->id ?? 1,
                    'patient_name' => $patients[0]->name ?? 'Ahmed Mohamed',
                    'patient_phone' => '01012345678',
                    'scan_type' => null,
                    'booking_date' => Carbon::now()->addDays(2),
                    'booking_time' => '09:00',
                    'status' => FacilityBooking::STATUS_CONFIRMED,
                    'notes' => 'Fasting blood work appointment',
                ],
                [
                    'type' => 'lab',
                    'lab_id' => $labs[1]->id ?? $labs[0]->id ?? null,
                    'radiology_center_id' => null,
                    'patient_id' => $patients[1]->id ?? 1,
                    'patient_name' => $patients[1]->name ?? 'Sara Ali',
                    'patient_phone' => '01098765432',
                    'scan_type' => null,
                    'booking_date' => Carbon::now()->addDays(3),
                    'booking_time' => '10:30',
                    'status' => FacilityBooking::STATUS_PENDING,
                    'notes' => 'Thyroid function test',
                ],
                [
                    'type' => 'lab',
                    'lab_id' => $labs[0]->id ?? null,
                    'radiology_center_id' => null,
                    'patient_id' => $patients[2]->id ?? 1,
                    'patient_name' => $patients[2]->name ?? 'Mohamed Hassan',
                    'patient_phone' => '01155566677',
                    'scan_type' => null,
                    'booking_date' => Carbon::now()->subDays(3),
                    'booking_time' => '08:30',
                    'status' => FacilityBooking::STATUS_COMPLETED,
                    'notes' => 'Complete blood count and lipid profile',
                ],
                // Radiology bookings
                [
                    'type' => 'radiology',
                    'lab_id' => null,
                    'radiology_center_id' => $centers[0]->id ?? null,
                    'patient_id' => $patients[0]->id ?? 1,
                    'patient_name' => $patients[0]->name ?? 'Ahmed Mohamed',
                    'patient_phone' => '01012345678',
                    'scan_type' => 'x-ray',
                    'booking_date' => Carbon::now()->addDays(1),
                    'booking_time' => '11:00',
                    'status' => FacilityBooking::STATUS_CONFIRMED,
                    'notes' => 'Chest X-ray follow-up',
                ],
                [
                    'type' => 'radiology',
                    'lab_id' => null,
                    'radiology_center_id' => $centers[1]->id ?? $centers[0]->id ?? null,
                    'patient_id' => $patients[3]->id ?? 1,
                    'patient_name' => $patients[3]->name ?? 'Fatma Ibrahim',
                    'patient_phone' => '01234567890',
                    'scan_type' => 'mri',
                    'booking_date' => Carbon::now()->addDays(5),
                    'booking_time' => '14:00',
                    'status' => FacilityBooking::STATUS_PENDING,
                    'notes' => 'Brain MRI - headache investigation',
                ],
                [
                    'type' => 'radiology',
                    'lab_id' => null,
                    'radiology_center_id' => $centers[2]->id ?? $centers[0]->id ?? null,
                    'patient_id' => $patients[4]->id ?? 1,
                    'patient_name' => $patients[4]->name ?? 'Omar Khaled',
                    'patient_phone' => '01177788899',
                    'scan_type' => 'ct',
                    'booking_date' => Carbon::now()->subDays(1),
                    'booking_time' => '09:30',
                    'status' => FacilityBooking::STATUS_COMPLETED,
                    'notes' => 'Abdominal CT scan',
                ],
                // Cancelled booking
                [
                    'type' => 'lab',
                    'lab_id' => $labs[2]->id ?? $labs[0]->id ?? null,
                    'radiology_center_id' => null,
                    'patient_id' => $patients[1]->id ?? 1,
                    'patient_name' => $patients[1]->name ?? 'Sara Ali',
                    'patient_phone' => '01098765432',
                    'scan_type' => null,
                    'booking_date' => Carbon::now()->subDays(5),
                    'booking_time' => '15:00',
                    'status' => FacilityBooking::STATUS_CANCELLED,
                    'notes' => 'Routine checkup',
                    'cancellation_reason' => 'Patient rescheduled to next week',
                    'cancelled_by' => $adminId,
                ],
                // No-show booking
                [
                    'type' => 'radiology',
                    'lab_id' => null,
                    'radiology_center_id' => $centers[3]->id ?? $centers[0]->id ?? null,
                    'patient_id' => $patients[2]->id ?? 1,
                    'patient_name' => $patients[2]->name ?? 'Mohamed Hassan',
                    'patient_phone' => '01155566677',
                    'scan_type' => 'ultrasound',
                    'booking_date' => Carbon::now()->subDays(2),
                    'booking_time' => '16:00',
                    'status' => FacilityBooking::STATUS_NO_SHOW,
                    'notes' => 'Abdominal ultrasound',
                    'admin_notes' => 'Patient did not show up. Attempted phone contact - no answer.',
                ],
            ];

            foreach ($bookings as $booking) {
                if ($booking['type'] === 'lab' && empty($booking['lab_id'])) {
                    continue;
                }
                if ($booking['type'] === 'radiology' && empty($booking['radiology_center_id'])) {
                    continue;
                }

                FacilityBooking::create($booking);
            }
        }
    }
}
