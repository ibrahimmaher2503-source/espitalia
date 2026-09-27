<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Appointment\Models\Appointment;
use Tests\TestCase;

class AppointmentSlotIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_appointment_slot_is_unique_and_cancellation_releases_it(): void
    {
        $patient = User::factory()->create();
        $attributes = [
            'user_id' => $patient->id,
            'doctor_id' => 9001,
            'booking_type' => 'clinic',
            'appointment_date' => '2030-01-15',
            'appointment_time' => '09:00',
            'start_date_time' => '2030-01-15 09:00:00',
            'status' => 'confirmed',
            'booking_slot_key' => Appointment::buildBookingSlotKey(9001, '2030-01-15', '09:00'),
        ];

        $first = Appointment::create($attributes);

        $duplicateRejected = false;
        try {
            Appointment::create($attributes);
        } catch (UniqueConstraintViolationException) {
            $duplicateRejected = true;
        }

        $this->assertTrue($duplicateRejected);
        $first->update(['status' => 'cancelled']);

        $replacement = Appointment::create($attributes);

        $this->assertNotSame($first->id, $replacement->id);
        $this->assertDatabaseHas('appointments', [
            'id' => $first->id,
            'status' => 'cancelled',
            'booking_slot_key' => null,
        ]);
    }
}
