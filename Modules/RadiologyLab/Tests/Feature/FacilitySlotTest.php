<?php

namespace Modules\RadiologyLab\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\RadiologyLab\Models\FacilityBooking;
use Modules\RadiologyLab\Models\FacilitySession;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Tests\Unit\BaseTest;

class FacilitySlotTest extends BaseTest
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'Modules/Location/database/migrations',
            '--force' => true,
        ]);
    }

    protected function createLab(array $overrides = []): Lab
    {
        return Lab::create(array_merge([
            'name_ar' => 'مختبر تجريبي',
            'name_en' => 'Test Lab',
            'status' => 1,
        ], $overrides));
    }

    protected function createSession(Lab $lab, array $overrides = []): FacilitySession
    {
        return FacilitySession::create(array_merge([
            'facility_type' => 'lab',
            'facility_id' => $lab->id,
            'day' => 'monday',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_duration' => 30,
            'is_holiday' => false,
            'status' => true,
        ], $overrides));
    }

    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    protected function nextMonday(): string
    {
        return now()->next('Monday')->format('Y-m-d');
    }

    public function test_slot_generation_returns_correct_intervals(): void
    {
        $lab = $this->createLab();
        $this->createSession($lab, [
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_duration' => 30,
        ]);

        $monday = $this->nextMonday();

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $slots = $response->json('data.slots');
        $this->assertCount(8, $slots); // 08:00–12:00 in 30min = 8 slots
        $this->assertEquals('08:00', $slots[0]['time']);
        $this->assertEquals('11:30', $slots[7]['time']);
    }

    public function test_breaks_are_excluded_from_slots(): void
    {
        $lab = $this->createLab();
        $this->createSession($lab, [
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'slot_duration' => 30,
            'breaks' => [
                ['start_break' => '10:00', 'end_break' => '10:30'],
            ],
        ]);

        $monday = $this->nextMonday();

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $response->assertStatus(200);

        $slots = $response->json('data.slots');
        $times = array_column($slots, 'time');
        $this->assertNotContains('10:00', $times);
        $this->assertCount(7, $slots); // 8 minus 1 break slot
    }

    public function test_booked_slots_are_marked_unavailable(): void
    {
        $lab = $this->createLab();
        $this->createSession($lab);
        $patient = $this->createUser();
        $monday = $this->nextMonday();

        FacilityBooking::create([
            'type' => 'lab',
            'lab_id' => $lab->id,
            'patient_id' => $patient->id,
            'patient_name' => 'Test Patient',
            'patient_phone' => '0500000000',
            'booking_date' => $monday,
            'booking_time' => '09:00:00',
            'status' => FacilityBooking::STATUS_PENDING,
        ]);

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $slots = $response->json('data.slots');
        $slot900 = collect($slots)->firstWhere('time', '09:00');
        $this->assertNotNull($slot900);
        $this->assertFalse($slot900['available']);
    }

    public function test_no_session_returns_empty_slots(): void
    {
        $lab = $this->createLab();
        $monday = $this->nextMonday();

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.slots'));
    }

    public function test_holiday_returns_empty_slots(): void
    {
        $lab = $this->createLab();
        $this->createSession($lab, ['is_holiday' => true]);
        $monday = $this->nextMonday();

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.slots'));
    }

    public function test_inactive_facility_returns_422(): void
    {
        $lab = $this->createLab(['status' => 0]);
        $this->createSession($lab);
        $monday = $this->nextMonday();

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$monday}");

        $response->assertStatus(422);
    }

    public function test_past_date_returns_422(): void
    {
        $lab = $this->createLab();
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $response = $this->getJson("/api/v1/facility-bookings/labs/{$lab->id}/slots?date={$yesterday}");

        $response->assertStatus(422);
    }
}
