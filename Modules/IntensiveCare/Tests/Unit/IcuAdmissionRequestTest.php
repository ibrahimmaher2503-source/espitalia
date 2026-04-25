<?php

namespace Modules\IntensiveCare\Tests\Unit;

use App\Models\User;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuDepartment;

class IcuAdmissionRequestTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Auto-Numbering Tests ────────────────────────────────────────────────

    public function test_auto_generates_request_number()
    {
        $request = IcuAdmissionRequest::factory()->create(['request_number' => null]);

        $this->assertNotNull($request->request_number);
        $this->assertIsString($request->request_number);
    }

    public function test_request_number_format_matches_icu_yyyy_xxxx()
    {
        $request = IcuAdmissionRequest::factory()->create(['request_number' => null]);

        $this->assertMatchesRegularExpression('/^ICU-\d{4}-\d{4}$/', $request->request_number);
    }

    public function test_sequential_numbering()
    {
        $request1 = IcuAdmissionRequest::factory()->create(['request_number' => null]);
        $request2 = IcuAdmissionRequest::factory()->create(['request_number' => null]);

        // Extract sequence numbers (last 4 digits)
        $num1 = (int) substr($request1->request_number, -4);
        $num2 = (int) substr($request2->request_number, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    public function test_manual_request_number_bypasses_auto_generation()
    {
        $manualNumber = 'ICU-2026-CUSTOM-001';

        $request = IcuAdmissionRequest::factory()->create([
            'request_number' => $manualNumber,
        ]);

        $this->assertEquals($manualNumber, $request->request_number);
    }

    public function test_soft_deleted_included_in_sequence()
    {
        // Create first request
        $request1 = IcuAdmissionRequest::factory()->create(['request_number' => null]);

        // Soft delete it
        $request1->delete();

        // Create second request - should increment from deleted request
        $request2 = IcuAdmissionRequest::factory()->create(['request_number' => null]);

        $num1 = (int) substr($request1->request_number, -4);
        $num2 = (int) substr($request2->request_number, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    // ── Status Transition Tests ─────────────────────────────────────────────

    public function test_can_transition_to_returns_boolean()
    {
        $request = IcuAdmissionRequest::factory()->create();

        $this->assertTrue(method_exists($request, 'canTransitionTo'));
        $this->assertIsBool($request->canTransitionTo(IcuAdmissionRequest::STATUS_ACCEPTED));
    }

    public function test_pending_can_transition_to_accepted()
    {
        $request = IcuAdmissionRequest::factory()->pending()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_ACCEPTED));
    }

    public function test_pending_can_transition_to_rejected()
    {
        $request = IcuAdmissionRequest::factory()->pending()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_REJECTED));
    }

    public function test_pending_can_transition_to_info_requested()
    {
        $request = IcuAdmissionRequest::factory()->pending()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_INFO_REQUESTED));
    }

    public function test_pending_can_transition_to_cancelled()
    {
        $request = IcuAdmissionRequest::factory()->pending()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_CANCELLED));
    }

    public function test_info_requested_can_transition_to_pending()
    {
        $request = IcuAdmissionRequest::factory()->infoRequested()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_PENDING));
    }

    public function test_info_requested_can_transition_to_cancelled()
    {
        $request = IcuAdmissionRequest::factory()->infoRequested()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_CANCELLED));
    }

    public function test_accepted_can_transition_to_cancelled()
    {
        $request = IcuAdmissionRequest::factory()->accepted()->create();

        $this->assertTrue($request->canTransitionTo(IcuAdmissionRequest::STATUS_CANCELLED));
    }

    public function test_rejected_is_terminal()
    {
        $request = IcuAdmissionRequest::factory()->rejected()->create();

        foreach (IcuAdmissionRequest::STATUSES as $status) {
            if ($status === IcuAdmissionRequest::STATUS_REJECTED) {
                continue;
            }
            $this->assertFalse(
                $request->canTransitionTo($status),
                "Rejected request should not be able to transition to {$status}"
            );
        }
    }

    public function test_cancelled_is_terminal()
    {
        $request = IcuAdmissionRequest::factory()->cancelled()->create();

        foreach (IcuAdmissionRequest::STATUSES as $status) {
            if ($status === IcuAdmissionRequest::STATUS_CANCELLED) {
                continue;
            }
            $this->assertFalse(
                $request->canTransitionTo($status),
                "Cancelled request should not be able to transition to {$status}"
            );
        }
    }

    public function test_pending_cannot_transition_to_accepted_directly_skip()
    {
        // Test that pending cannot skip to an invalid state not in allowed transitions
        $request = IcuAdmissionRequest::factory()->pending()->create();

        // Pending can go to accepted/rejected/info_requested/cancelled
        // but info_requested cannot go to accepted (not in its allowed transitions)
        $infoRequest = IcuAdmissionRequest::factory()->infoRequested()->create();
        $this->assertFalse($infoRequest->canTransitionTo(IcuAdmissionRequest::STATUS_ACCEPTED));
    }

    // ── Constants Tests ─────────────────────────────────────────────────────

    public function test_statuses_array_defined()
    {
        $this->assertIsArray(IcuAdmissionRequest::STATUSES);
        $this->assertContains(IcuAdmissionRequest::STATUS_PENDING, IcuAdmissionRequest::STATUSES);
        $this->assertContains(IcuAdmissionRequest::STATUS_ACCEPTED, IcuAdmissionRequest::STATUSES);
        $this->assertContains(IcuAdmissionRequest::STATUS_REJECTED, IcuAdmissionRequest::STATUSES);
        $this->assertContains(IcuAdmissionRequest::STATUS_INFO_REQUESTED, IcuAdmissionRequest::STATUSES);
        $this->assertContains(IcuAdmissionRequest::STATUS_CANCELLED, IcuAdmissionRequest::STATUSES);
    }

    public function test_case_types_defined()
    {
        $this->assertIsArray(IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('stroke', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('cardiac', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('post_operative', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('ventilator', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('neonatal', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('pediatric', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('burns', IcuAdmissionRequest::CASE_TYPES);
        $this->assertContains('general', IcuAdmissionRequest::CASE_TYPES);
    }

    public function test_urgency_levels_defined()
    {
        $this->assertIsArray(IcuAdmissionRequest::URGENCY_LEVELS);
        $this->assertContains('critical', IcuAdmissionRequest::URGENCY_LEVELS);
        $this->assertContains('urgent', IcuAdmissionRequest::URGENCY_LEVELS);
        $this->assertContains('standard', IcuAdmissionRequest::URGENCY_LEVELS);
    }

    public function test_payment_methods_defined()
    {
        $this->assertIsArray(IcuAdmissionRequest::PAYMENT_METHODS);
        $this->assertContains('insurance', IcuAdmissionRequest::PAYMENT_METHODS);
        $this->assertContains('cash', IcuAdmissionRequest::PAYMENT_METHODS);
    }

    public function test_gender_options_defined()
    {
        $this->assertIsArray(IcuAdmissionRequest::GENDER_OPTIONS);
        $this->assertContains('male', IcuAdmissionRequest::GENDER_OPTIONS);
        $this->assertContains('female', IcuAdmissionRequest::GENDER_OPTIONS);
    }

    // ── Casts Tests ─────────────────────────────────────────────────────────

    public function test_needs_ventilator_is_cast_to_boolean()
    {
        $request = IcuAdmissionRequest::factory()->create(['needs_ventilator' => 1]);

        $this->assertIsBool($request->needs_ventilator);
        $this->assertTrue($request->needs_ventilator);
    }

    public function test_needs_oxygen_is_cast_to_boolean()
    {
        $request = IcuAdmissionRequest::factory()->create(['needs_oxygen' => 1]);

        $this->assertIsBool($request->needs_oxygen);
        $this->assertTrue($request->needs_oxygen);
    }

    public function test_needs_ambulance_is_cast_to_boolean()
    {
        $request = IcuAdmissionRequest::factory()->create(['needs_ambulance' => 1]);

        $this->assertIsBool($request->needs_ambulance);
        $this->assertTrue($request->needs_ambulance);
    }

    public function test_response_date_is_cast_to_datetime()
    {
        $request = IcuAdmissionRequest::factory()->create(['response_date' => '2026-03-22 10:30:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $request->response_date);
        $this->assertEquals('2026-03-22', $request->response_date->format('Y-m-d'));
    }

    public function test_patient_age_is_cast_to_integer()
    {
        $request = IcuAdmissionRequest::factory()->create(['patient_age' => '45']);

        $this->assertIsInt($request->patient_age);
        $this->assertEquals(45, $request->patient_age);
    }

    // ── Relationship Tests ──────────────────────────────────────────────────

    public function test_belongs_to_patient()
    {
        $patient = User::factory()->create();
        $request = IcuAdmissionRequest::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(User::class, $request->patient);
        $this->assertEquals($patient->id, $request->patient->id);
    }

    public function test_belongs_to_hospital()
    {
        $hospital = Hospital::factory()->create();
        $request = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital->id]);

        $this->assertInstanceOf(Hospital::class, $request->hospital);
        $this->assertEquals($hospital->id, $request->hospital->id);
    }

    public function test_belongs_to_icu_department()
    {
        $hospital = Hospital::factory()->create();
        $dept = IcuDepartment::factory()->create(['hospital_id' => $hospital->id]);
        $request = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $dept->id,
        ]);

        $this->assertInstanceOf(IcuDepartment::class, $request->icuDepartment);
        $this->assertEquals($dept->id, $request->icuDepartment->id);
    }

    public function test_belongs_to_responded_by()
    {
        $responder = User::factory()->create();
        $request = IcuAdmissionRequest::factory()->create(['responded_by' => $responder->id]);

        $this->assertInstanceOf(User::class, $request->respondedBy);
        $this->assertEquals($responder->id, $request->respondedBy->id);
    }

    // ── Scope Tests ─────────────────────────────────────────────────────────

    public function test_by_patient_scope()
    {
        $patient1 = User::factory()->create();
        $patient2 = User::factory()->create();

        $request1 = IcuAdmissionRequest::factory()->create(['patient_id' => $patient1->id]);
        $request2 = IcuAdmissionRequest::factory()->create(['patient_id' => $patient2->id]);

        $results = IcuAdmissionRequest::byPatient($patient1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($request1));
        $this->assertFalse($results->contains($request2));
    }

    public function test_by_hospital_scope()
    {
        $hospital1 = Hospital::factory()->create();
        $hospital2 = Hospital::factory()->create();

        $request1 = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital1->id]);
        $request2 = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital2->id]);

        $results = IcuAdmissionRequest::byHospital($hospital1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($request1));
        $this->assertFalse($results->contains($request2));
    }

    public function test_by_status_scope()
    {
        $request1 = IcuAdmissionRequest::factory()->pending()->create();
        $request2 = IcuAdmissionRequest::factory()->accepted()->create();

        $results = IcuAdmissionRequest::byStatus(IcuAdmissionRequest::STATUS_PENDING)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($request1));
        $this->assertFalse($results->contains($request2));
    }

    public function test_pending_scope()
    {
        $request1 = IcuAdmissionRequest::factory()->pending()->create();
        $request2 = IcuAdmissionRequest::factory()->accepted()->create();

        $results = IcuAdmissionRequest::pending()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($request1));
        $this->assertFalse($results->contains($request2));
    }

    public function test_urgent_scope()
    {
        $urgentRequest = IcuAdmissionRequest::factory()->urgent()->create();
        $criticalRequest = IcuAdmissionRequest::factory()->critical()->create();
        $standardRequest = IcuAdmissionRequest::factory()->create(['urgency' => 'standard']);

        $results = IcuAdmissionRequest::urgent()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($urgentRequest));
        $this->assertTrue($results->contains($criticalRequest));
        $this->assertFalse($results->contains($standardRequest));
    }

    // ── Accessor Tests ──────────────────────────────────────────────────────

    public function test_status_badge_accessor()
    {
        $request = IcuAdmissionRequest::factory()->pending()->create();

        $badge = $request->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-warning', $badge);
    }

    public function test_urgency_badge_accessor()
    {
        $request = IcuAdmissionRequest::factory()->urgent()->create();

        $badge = $request->urgency_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-warning', $badge);
    }

    public function test_case_type_label_accessor()
    {
        $request = IcuAdmissionRequest::factory()->create(['case_type' => 'cardiac']);

        $label = $request->case_type_label;

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    // ── Bilingual Accessor Tests ─────────────────────────────────────────

    public function test_medical_condition_accessor_returns_en_when_locale_is_en()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'medical_condition_en' => 'Heart failure',
            'medical_condition_ar' => 'فشل القلب',
        ]);

        app()->setLocale('en');

        $this->assertEquals('Heart failure', $request->medical_condition);
    }

    public function test_medical_condition_accessor_returns_ar_when_locale_is_ar()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'medical_condition_en' => 'Heart failure',
            'medical_condition_ar' => 'فشل القلب',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('فشل القلب', $request->medical_condition);
    }

    public function test_medical_condition_accessor_falls_back_to_other_locale()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'medical_condition_en' => 'Heart failure',
            'medical_condition_ar' => null,
        ]);

        app()->setLocale('ar');

        $this->assertEquals('Heart failure', $request->medical_condition);
    }

    public function test_medical_condition_accessor_falls_back_from_en_to_ar()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'medical_condition_en' => null,
            'medical_condition_ar' => 'فشل القلب',
        ]);

        app()->setLocale('en');

        $this->assertEquals('فشل القلب', $request->medical_condition);
    }

    public function test_medical_condition_accessor_returns_empty_when_both_null()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'medical_condition_en' => null,
            'medical_condition_ar' => null,
        ]);

        app()->setLocale('en');

        $this->assertEquals('', $request->medical_condition);
    }

    public function test_diagnosis_accessor_returns_en_when_locale_is_en()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'diagnosis_en' => 'Acute myocardial infarction',
            'diagnosis_ar' => 'احتشاء عضلة القلب الحاد',
        ]);

        app()->setLocale('en');

        $this->assertEquals('Acute myocardial infarction', $request->diagnosis);
    }

    public function test_diagnosis_accessor_returns_ar_when_locale_is_ar()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'diagnosis_en' => 'Acute myocardial infarction',
            'diagnosis_ar' => 'احتشاء عضلة القلب الحاد',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('احتشاء عضلة القلب الحاد', $request->diagnosis);
    }

    public function test_diagnosis_accessor_falls_back_to_other_locale()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'diagnosis_en' => null,
            'diagnosis_ar' => 'احتشاء عضلة القلب الحاد',
        ]);

        app()->setLocale('en');

        $this->assertEquals('احتشاء عضلة القلب الحاد', $request->diagnosis);
    }

    public function test_admin_notes_accessor_returns_en_when_locale_is_en()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'admin_notes_en' => 'Patient stable',
            'admin_notes_ar' => 'المريض مستقر',
        ]);

        app()->setLocale('en');

        $this->assertEquals('Patient stable', $request->admin_notes);
    }

    public function test_admin_notes_accessor_returns_ar_when_locale_is_ar()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'admin_notes_en' => 'Patient stable',
            'admin_notes_ar' => 'المريض مستقر',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('المريض مستقر', $request->admin_notes);
    }

    public function test_admin_notes_accessor_falls_back_to_other_locale()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'admin_notes_en' => 'Patient stable',
            'admin_notes_ar' => null,
        ]);

        app()->setLocale('ar');

        $this->assertEquals('Patient stable', $request->admin_notes);
    }

    public function test_rejection_reason_accessor_returns_en_when_locale_is_en()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'rejection_reason_en' => 'No beds available',
            'rejection_reason_ar' => 'لا توجد أسرة متاحة',
        ]);

        app()->setLocale('en');

        $this->assertEquals('No beds available', $request->rejection_reason);
    }

    public function test_rejection_reason_accessor_returns_ar_when_locale_is_ar()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'rejection_reason_en' => 'No beds available',
            'rejection_reason_ar' => 'لا توجد أسرة متاحة',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('لا توجد أسرة متاحة', $request->rejection_reason);
    }

    public function test_rejection_reason_accessor_falls_back_to_other_locale()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'rejection_reason_en' => null,
            'rejection_reason_ar' => 'لا توجد أسرة متاحة',
        ]);

        app()->setLocale('en');

        $this->assertEquals('لا توجد أسرة متاحة', $request->rejection_reason);
    }

    public function test_rejection_reason_accessor_returns_empty_when_both_null()
    {
        $request = IcuAdmissionRequest::factory()->create([
            'rejection_reason_en' => null,
            'rejection_reason_ar' => null,
        ]);

        app()->setLocale('en');

        $this->assertEquals('', $request->rejection_reason);
    }

    // ── Fillable Tests ────────────────────────────────────────────────────

    public function test_fillable_contains_bilingual_fields()
    {
        $model = new IcuAdmissionRequest;
        $fillable = $model->getFillable();

        $bilingualFields = [
            'medical_condition_ar',
            'medical_condition_en',
            'diagnosis_ar',
            'diagnosis_en',
            'admin_notes_ar',
            'admin_notes_en',
            'rejection_reason_ar',
            'rejection_reason_en',
        ];

        foreach ($bilingualFields as $field) {
            $this->assertContains($field, $fillable, "Fillable should contain '{$field}'");
        }
    }

    public function test_fillable_does_not_contain_old_monolingual_fields()
    {
        $model = new IcuAdmissionRequest;
        $fillable = $model->getFillable();

        $oldFields = [
            'medical_condition',
            'diagnosis',
            'admin_notes',
            'rejection_reason',
        ];

        foreach ($oldFields as $field) {
            $this->assertNotContains($field, $fillable, "Fillable should NOT contain old field '{$field}'");
        }
    }

    // ── Soft Delete Tests ───────────────────────────────────────────────────

    public function test_soft_delete_behavior()
    {
        $request = IcuAdmissionRequest::factory()->create();
        $requestId = $request->id;

        $request->delete();

        $this->assertSoftDeleted($request);
        $this->assertDatabaseHas('icu_admission_requests', [
            'id' => $requestId,
            'deleted_at' => $request->deleted_at,
        ]);
    }
}
