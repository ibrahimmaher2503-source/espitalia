<?php

namespace Modules\Clinic\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Appointment\Models\Appointment;
use Modules\Clinic\Models\Doctor as DoctorRecord;
use Modules\Clinic\Tests\Unit\BaseTest;
use Modules\Clinic\Transformers\DoctorDetailsResource;

class BookingBrowseApiTest extends BaseTest
{
    private int $clinicId;
    private int $doctorId;
    private int $serviceId;
    private string $futureMonday;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runMigrations();
        if (! Schema::hasColumn('appointments', 'appointment_number')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('appointment_number')->nullable();
            });
        }
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('discount_type')->nullable();
            $table->decimal('discount_value')->nullable();
            $table->string('code')->nullable();
            $table->boolean('requires_coupon')->default(false);
            $table->boolean('auto_apply')->default(false);
            $table->string('short_label_ar')->nullable();
            $table->string('short_label_en')->nullable();
            $table->string('banner_text_ar')->nullable();
            $table->string('banner_text_en')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('status')->default('inactive');
            $table->boolean('applies_to_all_services')->default(false);
            $table->integer('priority')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('offer_services', function (Blueprint $table) {
            $table->unsignedBigInteger('offer_id');
            $table->unsignedBigInteger('service_id');
        });
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(false);
            $table->string('module_type')->nullable();
            $table->string('tax_type')->nullable();
            $table->softDeletes();
        });

        $doctor = User::factory()->create(['status' => 1]);
        $this->doctorId = $doctor->id;
        $now = now();
        $this->clinicId = DB::table('clinic')->insertGetId([
            'name' => 'QA Clinic', 'email' => 'clinic@example.test', 'slug' => 'qa-clinic',
            'contact_number' => '01000000000', 'address' => 'Cairo', 'status' => 1,
            'time_slot' => 15, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->serviceId = DB::table('clinics_services')->insertGetId([
            'name' => 'QA Consultation', 'type' => 'in_clinic', 'charges' => 200,
            'duration_min' => 30, 'time_slot' => 15, 'status' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('doctors')->insert([
            'doctor_id' => $this->doctorId, 'status' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('doctor_clinic_mapping')->insert([
            'doctor_id' => $this->doctorId, 'clinic_id' => $this->clinicId,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('clinic_service_mapping')->insert([
            'clinic_id' => $this->clinicId, 'service_id' => $this->serviceId,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('doctor_service_mappings')->insert([
            'doctor_id' => $this->doctorId, 'clinic_id' => $this->clinicId,
            'service_id' => $this->serviceId, 'charges' => 200, 'status' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->futureMonday = Carbon::now()->startOfDay()->next(Carbon::MONDAY)->toDateString();
        DB::table('doctor_session')->insert([
            'doctor_id' => $this->doctorId, 'clinic_id' => $this->clinicId,
            'day' => 'monday', 'start_time' => '09:00', 'end_time' => '10:00',
            'is_holiday' => 0, 'breaks' => '[]', 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function test_public_browse_contract_reaches_future_slots_without_sensitive_doctor_fields(): void
    {
        $this->getJson("/api/get-service-list?clinic_id={$this->clinicId}")
            ->assertOk()->assertJsonPath('data.0.id', $this->serviceId);
        $this->getJson("/api/get-clinic-list?service_id={$this->serviceId}")
            ->assertOk()->assertJsonPath('data.0.id', $this->clinicId);
        $doctor = DoctorRecord::where('doctor_id', $this->doctorId)->firstOrFail();
        $doctor->setRelation('user', User::findOrFail($this->doctorId));
        $doctor->setRelation('doctorService', collect());
        $doctor->setRelation('doctorclinic', collect());
        $doctor->setRelation('doctorCommission', collect());
        $doctor->setRelation('doctorReviews', collect());
        $doctor->setRelation('doctorDocuments', collect());
        $details = (new DoctorDetailsResource($doctor))->resolve();
        $this->assertArrayNotHasKey('player_id', $details);
        $this->assertArrayNotHasKey('is_banned', $details);
        $this->assertArrayNotHasKey('is_manager', $details);

        Appointment::create([
            'user_id' => $this->doctorId, 'booking_type' => 'clinic',
            'clinic_id' => $this->clinicId, 'doctor_id' => $this->doctorId,
            'service_id' => $this->serviceId, 'appointment_date' => $this->futureMonday,
            'appointment_time' => '09:15', 'start_date_time' => "{$this->futureMonday} 09:15:00",
            'duration' => 30, 'status' => 'confirmed',
        ]);

        $this->getJson("/api/get-time-slots?appointment_date={$this->futureMonday}&clinic_id={$this->clinicId}&doctor_id={$this->doctorId}&service_id={$this->serviceId}")
            ->assertOk()->assertJsonPath('status', true)
            ->assertJsonFragment(['09:00'])->assertJsonFragment(['09:45'])
            ->assertJsonMissing(['09:15'])->assertJsonMissing(['09:30']);
    }

    public function test_slots_reject_past_and_mismatched_browse_inputs(): void
    {
        $this->getJson("/api/get-time-slots?appointment_date=".now()->subDay()->toDateString()."&clinic_id={$this->clinicId}&doctor_id={$this->doctorId}&service_id={$this->serviceId}")
            ->assertUnprocessable()->assertJsonPath('status', false);

        $unmappedServiceId = DB::table('clinics_services')->insertGetId([
            'name' => 'Unmapped', 'type' => 'in_clinic', 'charges' => 100,
            'duration_min' => 15, 'time_slot' => 15, 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->getJson("/api/get-time-slots?appointment_date={$this->futureMonday}&clinic_id={$this->clinicId}&doctor_id={$this->doctorId}&service_id={$unmappedServiceId}")
            ->assertUnprocessable()->assertJsonPath('status', false);
    }
}
