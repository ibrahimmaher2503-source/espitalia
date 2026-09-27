<?php

namespace Modules\Clinic\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorIndependentService;
use Modules\Clinic\Tests\Unit\BaseTest;

class IndependentBookingApiTest extends BaseTest
{
    private User $patient;
    private Doctor $doctor;
    private DoctorIndependentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
        if (DB::connection()->getDriverName() === 'sqlite' && ! Schema::hasTable('commissions')) {
            Schema::create('commissions', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('commission_type')->nullable();
                $table->decimal('commission_value', 8, 2)->default(0);
                $table->string('type')->nullable();
                $table->string('commissionable')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
        if (DB::connection()->getDriverName() === 'sqlite' && ! Schema::hasTable('employee_commissions')) {
            Schema::create('employee_commissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('commission_id');
                $table->softDeletes();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
                $table->id();
                $table->boolean('status')->default(false);
                $table->string('module_type')->nullable();
                $table->string('tax_type')->nullable();
                $table->softDeletes();
            });
        }
        if (! Schema::hasColumn('appointments', 'booking_slot_key')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('booking_slot_key', 150)->nullable();
                $table->unique('booking_slot_key', 'appointments_booking_slot_key_unique');
            });
        }
        if (! Schema::hasColumn('appointments', 'appointment_number')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('appointment_number')->nullable();
            });
        }

        $this->patient = User::factory()->create(['status' => 1, 'user_type' => 'user']);
        $doctorUser = User::factory()->create(['status' => 1]);
        $this->doctor = Doctor::create([
            'doctor_id' => $doctorUser->id,
            'vendor_id' => $doctorUser->id,
            'status' => 1,
            'is_independent' => true,
        ]);
        $this->service = DoctorIndependentService::create([
            'doctor_id' => $doctorUser->id,
            'name' => 'Independent consultation',
            'duration_min' => 30,
            'time_slot' => 30,
            'charges' => 100,
            'status' => 1,
        ]);
    }

    public function test_slots_reject_service_owned_by_another_doctor_or_inactive_service(): void
    {
        $otherUser = User::factory()->create(['status' => 1]);
        $otherDoctor = Doctor::create([
            'doctor_id' => $otherUser->id,
            'vendor_id' => $otherUser->id,
            'status' => 1,
            'is_independent' => true,
        ]);
        $otherService = DoctorIndependentService::create([
            'doctor_id' => $otherUser->id,
            'name' => 'Other consultation',
            'status' => 1,
        ]);
        $date = now()->addDay()->toDateString();

        $this->actingAs($this->patient, 'sanctum')
            ->postJson("/api/independent-doctors/{$this->doctor->id}/slots", [
                'appointment_date' => $date,
                'independent_service_id' => $otherService->id,
            ])->assertNotFound();

        $this->service->update(['status' => 0]);
        $this->postJson("/api/independent-doctors/{$this->doctor->id}/slots", [
            'appointment_date' => $date,
            'independent_service_id' => $this->service->id,
        ])->assertNotFound();
    }

    public function test_duplicate_independent_booking_is_rejected_by_slot_key(): void
    {
        $date = now()->addDay()->toDateString();
        $payload = [
            'doctor_id' => $this->doctor->id,
            'independent_service_id' => $this->service->id,
            'appointment_date' => $date,
            'appointment_time' => '09:00',
        ];

        $this->actingAs($this->patient, 'sanctum')
            ->postJson('/api/independent-booking', $payload, ['Idempotency-Key' => 'independent-first'])
            ->assertOk();

        $this->postJson('/api/independent-booking', $payload, ['Idempotency-Key' => 'independent-second'])
            ->assertUnprocessable()
            ->assertJsonPath('status', false);

        $this->assertSame(1, DB::table('appointments')->where('booking_type', 'independent')->count());
    }

    public function test_patient_can_list_and_view_owned_independent_booking_only(): void
    {
        $date = now()->addDay()->toDateString();
        $this->actingAs($this->patient, 'sanctum')
            ->postJson('/api/independent-booking', [
                'doctor_id' => $this->doctor->id,
                'independent_service_id' => $this->service->id,
                'appointment_date' => $date,
                'appointment_time' => '10:00',
            ], ['Idempotency-Key' => 'independent-list-detail'])
            ->assertOk();

        $appointment = DB::table('appointments')->where('user_id', $this->patient->id)->latest('id')->first();

        $this->getJson('/api/appointment-list')->assertOk()
            ->assertJsonPath('data.0.booking_type', 'independent')
            ->assertJsonPath('data.0.independent_service_id', $this->service->id)
            ->assertJsonPath('data.0.service_name', 'Independent consultation');

        $this->getJson('/api/appointment-detail?appointment_id='.$appointment->id)->assertOk()
            ->assertJsonPath('data.booking_type', 'independent')
            ->assertJsonPath('data.independent_service_id', $this->service->id)
            ->assertJsonPath('data.service_name', 'Independent consultation');

        $otherPatient = User::factory()->create(['status' => 1, 'user_type' => 'user']);
        $this->actingAs($otherPatient, 'sanctum')
            ->getJson('/api/appointment-detail?appointment_id='.$appointment->id)
            ->assertNotFound();
    }
}
