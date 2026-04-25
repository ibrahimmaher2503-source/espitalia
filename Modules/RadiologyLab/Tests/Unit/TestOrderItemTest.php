<?php

namespace Modules\RadiologyLab\Tests\Unit;

use App\Models\User;
use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderItem;

class TestOrderItemTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Attribute Casting Tests (T032) ─────────────────────────────────────────

    /**
     * T032: Test TestOrderItem attribute casting (result_date datetime, price float)
     */
    public function test_result_date_is_cast_to_datetime()
    {
        $item = TestOrderItem::factory()->create([
            'result_date' => '2026-03-11 14:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $item->result_date);
        $this->assertEquals('2026-03-11 14:30:00', $item->result_date->format('Y-m-d H:i:s'));
    }

    public function test_price_is_cast_to_decimal()
    {
        $item = TestOrderItem::factory()->create(['price' => '125.50']);

        $this->assertIsNumeric($item->price);
        $this->assertEquals('125.50', $item->price);
    }

    // ── Status Constants Tests (T033) ──────────────────────────────────────────

    /**
     * T033: Test TestOrderItem status constants defined
     */
    public function test_status_constants_defined()
    {
        $this->assertEquals('pending', TestOrderItem::STATUS_PENDING);
        $this->assertEquals('sample_collected', TestOrderItem::STATUS_SAMPLE_COLLECTED);
        $this->assertEquals('processing', TestOrderItem::STATUS_PROCESSING);
        $this->assertEquals('completed', TestOrderItem::STATUS_COMPLETED);
    }

    // ── Relationship Tests (T034) ───────────────────────────────────────────────

    /**
     * T034: Test TestOrderItem relationships (testOrder, labTest, resultEnteredBy)
     */
    public function test_test_order_item_belongs_to_test_order()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $this->assertInstanceOf(TestOrder::class, $item->testOrder);
        $this->assertEquals($order->id, $item->testOrder->id);
    }

    public function test_test_order_item_belongs_to_lab_test()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $this->assertInstanceOf(LabTest::class, $item->labTest);
        $this->assertEquals($labTest->id, $item->labTest->id);
    }

    public function test_test_order_item_belongs_to_result_entered_by_user()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $technician = User::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_by' => $technician->id,
        ]);

        $this->assertInstanceOf(User::class, $item->resultEnteredBy);
        $this->assertEquals($technician->id, $item->resultEnteredBy->id);
    }

    // ── Soft Delete Tests (T035) ────────────────────────────────────────────────

    /**
     * T035: Test TestOrderItem soft delete behavior
     */
    public function test_soft_delete_behavior()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);
        $itemId = $item->id;

        $item->delete();

        $this->assertSoftDeleted($item);
        $this->assertDatabaseHas('test_order_items', [
            'id' => $itemId,
            'deleted_at' => $item->deleted_at,
        ]);
    }

    // ── Scope Tests (T073-T075) ─────────────────────────────────────────────────

    /**
     * T073: Test pending() scope filters status='pending'
     */
    public function test_pending_scope_filters_status_pending()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();

        $pendingItem = TestOrderItem::factory()->pending()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);
        $completedItem = TestOrderItem::factory()->completed()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $results = TestOrderItem::pending()->get();

        $this->assertTrue($results->contains($pendingItem));
        $this->assertFalse($results->contains($completedItem));
    }

    /**
     * T074: Test completed() scope filters status='completed'
     */
    public function test_completed_scope_filters_status_completed()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();

        $pendingItem = TestOrderItem::factory()->pending()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);
        $completedItem = TestOrderItem::factory()->completed()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $results = TestOrderItem::completed()->get();

        $this->assertFalse($results->contains($pendingItem));
        $this->assertTrue($results->contains($completedItem));
    }

    /**
     * T075: Test withResults() scope filters non-null result_value
     */
    public function test_with_results_scope_filters_non_null_result_value()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();

        $itemWithResult = TestOrderItem::factory()->withResults()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_value' => '120 mg/dL',
        ]);
        $itemWithoutResult = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_value' => null,
        ]);

        $results = TestOrderItem::withResults()->get();

        $this->assertTrue($results->contains($itemWithResult));
        $this->assertFalse($results->contains($itemWithoutResult));
    }

    // ── Accessor Tests (T082-T083) ──────────────────────────────────────────────

    /**
     * T082: Test result_status_badge accessor returns HTML badge
     */
    public function test_result_status_badge_accessor_returns_html_badge()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_status' => 'normal',
        ]);

        $badge = $item->result_status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-success', $badge);
    }

    /**
     * T083: Test has_result accessor returns boolean based on result_value
     */
    public function test_has_result_accessor_returns_boolean()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();

        $itemWithResult = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_value' => '120 mg/dL',
        ]);
        $itemWithoutResult = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_value' => null,
        ]);

        $this->assertTrue($itemWithResult->has_result);
        $this->assertFalse($itemWithoutResult->has_result);
    }

    // ── Edge Case Tests (T094-T097) ─────────────────────────────────────────────

    /**
     * T094: Test null result_date handled gracefully
     */
    public function test_null_result_date_handled_gracefully()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_date' => null,
        ]);

        $this->assertNull($item->result_date);
    }

    /**
     * T095: Test result_status_badge with null result_status
     */
    public function test_result_status_badge_with_null_result_status()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_status' => null,
        ]);

        $badge = $item->result_status_badge;

        $this->assertEquals('-', $badge);
    }

    /**
     * T096: Test has_result with empty string result_value
     */
    public function test_has_result_with_empty_string_result_value()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
            'result_value' => '',
        ]);

        $this->assertFalse($item->has_result);
    }

    /**
     * T097: Test deleted lab test relationship returns null
     */
    public function test_deleted_lab_test_relationship_returns_null()
    {
        $order = TestOrder::factory()->create();
        $labTest = LabTest::factory()->create();
        $item = TestOrderItem::factory()->create([
            'test_order_id' => $order->id,
            'lab_test_id' => $labTest->id,
        ]);

        $labTest->delete();

        $this->assertNull($item->fresh()->labTest);
    }
}
