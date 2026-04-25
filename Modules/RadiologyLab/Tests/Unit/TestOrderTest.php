<?php

namespace Modules\RadiologyLab\Tests\Unit;

use App\Models\User;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderItem;
use Modules\RadiologyLab\Models\TestReport;

class TestOrderTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Order Number Auto-Generation Tests (T042-T048) ────────────────────────

    /**
     * T042: Test TestOrder auto-generates order_number on creation
     */
    public function test_auto_generates_order_number_on_creation()
    {
        $order = TestOrder::factory()->create(['order_number' => null]);

        $this->assertNotNull($order->order_number);
        $this->assertIsString($order->order_number);
    }

    /**
     * T043: Test order_number format matches LAB-YYYY-XXXX pattern
     */
    public function test_order_number_format_matches_lab_yyyy_xxxx_pattern()
    {
        $order = TestOrder::factory()->create(['order_number' => null]);

        $this->assertMatchesRegularExpression('/^LAB-\d{4}-\d{4}$/', $order->order_number);
    }

    /**
     * T044: Test sequential order numbering (order 2 = order 1 + 1)
     */
    public function test_sequential_order_numbering()
    {
        $order1 = TestOrder::factory()->create(['order_number' => null]);
        $order2 = TestOrder::factory()->create(['order_number' => null]);

        // Extract sequence numbers (last 4 digits)
        $num1 = (int) substr($order1->order_number, -4);
        $num2 = (int) substr($order2->order_number, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    /**
     * T045: Test first order of year generates LAB-YYYY-0001
     */
    public function test_first_order_of_year_generates_lab_yyyy_0001()
    {
        // Create an order with explicit order_number to simulate first order
        $order = TestOrder::factory()->create(['order_number' => null]);

        $expectedPattern = '/^LAB-'.now()->format('Y').'-0001$/';
        $this->assertMatchesRegularExpression($expectedPattern, $order->order_number);
    }

    /**
     * T046: Test order number after 9999 generates LAB-YYYY-10000 (5 digits)
     */
    public function test_order_number_after_9999_generates_lab_yyyy_10000()
    {
        $year = now()->format('Y');

        // Create order with number 9999
        TestOrder::factory()->create([
            'order_number' => "LAB-{$year}-9999",
        ]);

        // Next order should be 10000 (5 digits, not 4)
        $nextOrder = TestOrder::factory()->create(['order_number' => null]);

        $this->assertEquals("LAB-{$year}-10000", $nextOrder->order_number);
    }

    /**
     * T047: Test manual order_number bypasses auto-generation
     */
    public function test_manual_order_number_bypasses_auto_generation()
    {
        $manualNumber = 'LAB-2026-CUSTOM-001';

        $order = TestOrder::factory()->create([
            'order_number' => $manualNumber,
        ]);

        $this->assertEquals($manualNumber, $order->order_number);
    }

    /**
     * T048: Test soft-deleted orders included in sequence calculation
     */
    public function test_soft_deleted_orders_included_in_sequence_calculation()
    {
        $year = now()->format('Y');

        // Create first order
        $order1 = TestOrder::factory()->create(['order_number' => null]);

        // Soft delete it
        $order1->delete();

        // Create second order - should increment from deleted order
        $order2 = TestOrder::factory()->create(['order_number' => null]);

        $num1 = (int) substr($order1->order_number, -4);
        $num2 = (int) substr($order2->order_number, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    // ── Status Transition Tests (T049-T062) ───────────────────────────────────

    /**
     * T049: Test canTransitionTo() method exists and returns boolean
     */
    public function test_can_transition_to_method_exists_and_returns_boolean()
    {
        $order = TestOrder::factory()->create();

        $this->assertTrue(method_exists($order, 'canTransitionTo'));
        $this->assertIsBool($order->canTransitionTo(TestOrder::STATUS_CONFIRMED));
    }

    /**
     * T050: Test pending can transition to confirmed
     */
    public function test_pending_can_transition_to_confirmed()
    {
        $order = TestOrder::factory()->pending()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CONFIRMED));
    }

    /**
     * T051: Test pending can transition to cancelled
     */
    public function test_pending_can_transition_to_cancelled()
    {
        $order = TestOrder::factory()->pending()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CANCELLED));
    }

    /**
     * T052: Test pending cannot transition to completed (skip intermediate)
     */
    public function test_pending_cannot_transition_to_completed_skip_intermediate()
    {
        $order = TestOrder::factory()->pending()->create();

        $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_COMPLETED));
    }

    /**
     * T053: Test confirmed can transition to sample_collected
     */
    public function test_confirmed_can_transition_to_sample_collected()
    {
        $order = TestOrder::factory()->confirmed()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_SAMPLE_COLLECTED));
    }

    /**
     * T054: Test confirmed can transition to cancelled
     */
    public function test_confirmed_can_transition_to_cancelled()
    {
        $order = TestOrder::factory()->confirmed()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CANCELLED));
    }

    /**
     * T055: Test sample_collected can transition to processing
     */
    public function test_sample_collected_can_transition_to_processing()
    {
        $order = TestOrder::factory()->sampleCollected()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_PROCESSING));
    }

    /**
     * T056: Test sample_collected can transition to cancelled
     */
    public function test_sample_collected_can_transition_to_cancelled()
    {
        $order = TestOrder::factory()->sampleCollected()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_CANCELLED));
    }

    /**
     * T057: Test processing can transition to completed
     */
    public function test_processing_can_transition_to_completed()
    {
        $order = TestOrder::factory()->processing()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_COMPLETED));
    }

    /**
     * T058: Test processing cannot transition to cancelled
     */
    public function test_processing_cannot_transition_to_cancelled()
    {
        $order = TestOrder::factory()->processing()->create();

        $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_CANCELLED));
    }

    /**
     * T059: Test completed can transition to delivered
     */
    public function test_completed_can_transition_to_delivered()
    {
        $order = TestOrder::factory()->completed()->create();

        $this->assertTrue($order->canTransitionTo(TestOrder::STATUS_DELIVERED));
    }

    /**
     * T060: Test completed cannot transition to cancelled
     */
    public function test_completed_cannot_transition_to_cancelled()
    {
        $order = TestOrder::factory()->completed()->create();

        $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_CANCELLED));
    }

    /**
     * T061: Test delivered status is terminal (cannot transition to any status)
     */
    public function test_delivered_status_is_terminal_cannot_transition_to_any_status()
    {
        $order = TestOrder::factory()->delivered()->create();

        foreach (TestOrder::STATUSES as $status) {
            if ($status === TestOrder::STATUS_DELIVERED) {
                continue;
            }
            $this->assertFalse(
                $order->canTransitionTo($status),
                "Delivered order should not be able to transition to {$status}"
            );
        }
    }

    /**
     * T062: Test cancelled status is terminal (cannot transition to any status)
     */
    public function test_cancelled_status_is_terminal_cannot_transition_to_any_status()
    {
        $order = TestOrder::factory()->cancelled()->create();

        foreach (TestOrder::STATUSES as $status) {
            if ($status === TestOrder::STATUS_CANCELLED) {
                continue;
            }
            $this->assertFalse(
                $order->canTransitionTo($status),
                "Cancelled order should not be able to transition to {$status}"
            );
        }
    }

    // ── Additional Status and Priority Constants Tests ────────────────────────

    public function test_status_constants_defined()
    {
        $this->assertEquals('pending', TestOrder::STATUS_PENDING);
        $this->assertEquals('confirmed', TestOrder::STATUS_CONFIRMED);
        $this->assertEquals('sample_collected', TestOrder::STATUS_SAMPLE_COLLECTED);
        $this->assertEquals('processing', TestOrder::STATUS_PROCESSING);
        $this->assertEquals('completed', TestOrder::STATUS_COMPLETED);
        $this->assertEquals('delivered', TestOrder::STATUS_DELIVERED);
        $this->assertEquals('cancelled', TestOrder::STATUS_CANCELLED);
    }

    public function test_statuses_array_contains_all_status_constants()
    {
        $this->assertContains(TestOrder::STATUS_PENDING, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_CONFIRMED, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_SAMPLE_COLLECTED, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_PROCESSING, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_COMPLETED, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_DELIVERED, TestOrder::STATUSES);
        $this->assertContains(TestOrder::STATUS_CANCELLED, TestOrder::STATUSES);
    }

    public function test_priority_constants_defined()
    {
        $this->assertEquals('routine', TestOrder::PRIORITY_ROUTINE);
        $this->assertEquals('urgent', TestOrder::PRIORITY_URGENT);
        $this->assertEquals('stat', TestOrder::PRIORITY_STAT);
    }

    public function test_priorities_array_contains_all_priority_constants()
    {
        $this->assertContains(TestOrder::PRIORITY_ROUTINE, TestOrder::PRIORITIES);
        $this->assertContains(TestOrder::PRIORITY_URGENT, TestOrder::PRIORITIES);
        $this->assertContains(TestOrder::PRIORITY_STAT, TestOrder::PRIORITIES);
    }

    // ── Attribute Casting Tests ─────────────────────────────────────────────────

    public function test_order_date_is_cast_to_date()
    {
        $order = TestOrder::factory()->create(['order_date' => '2026-03-11']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $order->order_date);
        $this->assertEquals('2026-03-11', $order->order_date->format('Y-m-d'));
    }

    public function test_total_amount_is_cast_to_decimal()
    {
        $order = TestOrder::factory()->create(['total_amount' => '150.50']);

        $this->assertIsNumeric($order->total_amount);
        $this->assertEquals('150.50', $order->total_amount);
    }

    public function test_discount_amount_is_cast_to_decimal()
    {
        $order = TestOrder::factory()->create(['discount_amount' => '25.00']);

        $this->assertIsNumeric($order->discount_amount);
        $this->assertEquals('25.00', $order->discount_amount);
    }

    public function test_final_amount_is_cast_to_decimal()
    {
        $order = TestOrder::factory()->create(['final_amount' => '125.50']);

        $this->assertIsNumeric($order->final_amount);
        $this->assertEquals('125.50', $order->final_amount);
    }

    // ── Relationship Tests ────────────────────────────────────────────────────

    public function test_test_order_belongs_to_patient()
    {
        $patient = User::factory()->create();
        $order = TestOrder::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(User::class, $order->patient);
        $this->assertEquals($patient->id, $order->patient->id);
    }

    public function test_test_order_belongs_to_doctor()
    {
        $doctor = User::factory()->create();
        $order = TestOrder::factory()->create(['doctor_id' => $doctor->id]);

        $this->assertInstanceOf(User::class, $order->doctor);
        $this->assertEquals($doctor->id, $order->doctor->id);
    }

    public function test_test_order_belongs_to_lab_technician()
    {
        $technician = User::factory()->create();
        $order = TestOrder::factory()->create(['lab_technician_id' => $technician->id]);

        $this->assertInstanceOf(User::class, $order->labTechnician);
        $this->assertEquals($technician->id, $order->labTechnician->id);
    }

    public function test_test_order_has_many_items()
    {
        $labTest = \Modules\RadiologyLab\Models\LabTest::factory()->create();
        $order = TestOrder::factory()->create();
        $item1 = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);
        $item2 = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $this->assertCount(2, $order->items);
        $this->assertTrue($order->items->contains('id', $item1->id));
        $this->assertTrue($order->items->contains('id', $item2->id));
    }

    public function test_test_order_has_many_reports()
    {
        $order = TestOrder::factory()->create();
        $report1 = TestReport::factory()->create(['test_order_id' => $order->id]);
        $report2 = TestReport::factory()->create(['test_order_id' => $order->id]);

        $this->assertCount(2, $order->reports);
        $this->assertTrue($order->reports->contains('id', $report1->id));
        $this->assertTrue($order->reports->contains('id', $report2->id));
    }

    // ── Scope Tests ─────────────────────────────────────────────────────────

    public function test_by_patient_scope_filters_by_patient_id()
    {
        $patient1 = User::factory()->create();
        $patient2 = User::factory()->create();

        $order1 = TestOrder::factory()->create(['patient_id' => $patient1->id]);
        $order2 = TestOrder::factory()->create(['patient_id' => $patient2->id]);

        $results = TestOrder::byPatient($patient1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($order1));
        $this->assertFalse($results->contains($order2));
    }

    public function test_by_technician_scope_filters_by_lab_technician_id()
    {
        $technician1 = User::factory()->create();
        $technician2 = User::factory()->create();

        $order1 = TestOrder::factory()->create(['lab_technician_id' => $technician1->id]);
        $order2 = TestOrder::factory()->create(['lab_technician_id' => $technician2->id]);

        $results = TestOrder::byTechnician($technician1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($order1));
        $this->assertFalse($results->contains($order2));
    }

    public function test_by_doctor_scope_filters_by_doctor_id()
    {
        $doctor1 = User::factory()->create();
        $doctor2 = User::factory()->create();

        $order1 = TestOrder::factory()->create(['doctor_id' => $doctor1->id]);
        $order2 = TestOrder::factory()->create(['doctor_id' => $doctor2->id]);

        $results = TestOrder::byDoctor($doctor1->id)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($order1));
        $this->assertFalse($results->contains($order2));
    }

    public function test_by_status_scope_filters_by_status()
    {
        $order1 = TestOrder::factory()->pending()->create();
        $order2 = TestOrder::factory()->confirmed()->create();

        $results = TestOrder::byStatus(TestOrder::STATUS_PENDING)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($order1));
        $this->assertFalse($results->contains($order2));
    }

    public function test_pending_scope_filters_status_pending()
    {
        $order1 = TestOrder::factory()->pending()->create();
        $order2 = TestOrder::factory()->confirmed()->create();

        $results = TestOrder::pending()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($order1));
        $this->assertFalse($results->contains($order2));
    }

    // ── Accessor Tests ────────────────────────────────────────────────────────

    public function test_status_badge_accessor_returns_html_badge()
    {
        $order = TestOrder::factory()->pending()->create();

        $badge = $order->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-warning', $badge);
    }

    public function test_payment_badge_accessor_returns_html_badge()
    {
        $order = TestOrder::factory()->create(['payment_status' => 'paid']);

        $badge = $order->payment_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-success', $badge);
    }

    public function test_priority_badge_accessor_returns_html_badge()
    {
        $order = TestOrder::factory()->urgent()->create();

        $badge = $order->priority_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-warning', $badge);
    }

    // ── Soft Delete Tests ───────────────────────────────────────────────────

    public function test_soft_delete_behavior()
    {
        $order = TestOrder::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted($order);
        $this->assertDatabaseHas('test_orders', [
            'id' => $orderId,
            'deleted_at' => $order->deleted_at,
        ]);
    }

    // ── Edge Case Tests ─────────────────────────────────────────────────────

    public function test_deleted_patient_relationship_returns_null()
    {
        $patient = User::factory()->create();
        $order = TestOrder::factory()->create(['patient_id' => $patient->id]);

        $patient->delete();

        $this->assertNull($order->fresh()->patient);
    }

    public function test_deleted_doctor_relationship_returns_null()
    {
        $doctor = User::factory()->create();
        $order = TestOrder::factory()->create(['doctor_id' => $doctor->id]);

        $doctor->delete();

        $this->assertNull($order->fresh()->doctor);
    }

    public function test_order_number_generation_with_year_rollover()
    {
        // Create order in 2025
        $order2025 = TestOrder::factory()->create([
            'order_number' => 'LAB-2025-9999',
            'order_date' => '2025-12-31',
        ]);

        // Create order in 2026 (new year)
        $order2026 = TestOrder::factory()->create([
            'order_number' => null,
            'order_date' => '2026-01-01',
        ]);

        // Should reset to 0001 for new year
        $this->assertEquals('LAB-2026-0001', $order2026->order_number);
    }

    public function test_can_transition_to_with_null_status()
    {
        $order = TestOrder::factory()->make(['status' => null]);

        $this->assertFalse($order->canTransitionTo(TestOrder::STATUS_CONFIRMED));
    }

    public function test_badge_accessors_with_invalid_status_values()
    {
        $order = TestOrder::factory()->create(['status' => 'invalid_status']);

        $badge = $order->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-secondary', $badge);
        $this->assertStringContainsString('invalid_status', $badge);
    }
}
