<?php

namespace Modules\RadiologyLab\Tests\Unit;

use App\Models\User;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestReport;

class TestReportTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Attribute Casting Tests (T037) ─────────────────────────────────────────

    /**
     * T037: Test TestReport attribute casting (report_date date)
     */
    public function test_report_date_is_cast_to_date()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create([
            'test_order_id' => $order->id,
            'report_date' => '2026-03-11',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $report->report_date);
        $this->assertEquals('2026-03-11', $report->report_date->format('Y-m-d'));
    }

    // ── Type and Status Constants Tests (T038) ─────────────────────────────────

    /**
     * T038: Test TestReport type and status constants defined
     */
    public function test_type_constants_defined()
    {
        $this->assertEquals('lab_report', TestReport::TYPE_LAB_REPORT);
        $this->assertEquals('radiology_report', TestReport::TYPE_RADIOLOGY_REPORT);
        $this->assertEquals('custom', TestReport::TYPE_CUSTOM);
    }

    public function test_status_constants_defined()
    {
        $this->assertEquals('draft', TestReport::STATUS_DRAFT);
        $this->assertEquals('final', TestReport::STATUS_FINAL);
        $this->assertEquals('amended', TestReport::STATUS_AMENDED);
    }

    // ── Relationship Tests (T039) ───────────────────────────────────────────────

    /**
     * T039: Test TestReport relationships (testOrder, generatedBy)
     */
    public function test_test_report_belongs_to_test_order()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);

        $this->assertInstanceOf(TestOrder::class, $report->testOrder);
        $this->assertEquals($order->id, $report->testOrder->id);
    }

    public function test_test_report_belongs_to_generated_by_user()
    {
        $order = TestOrder::factory()->create();
        $technician = User::factory()->create();
        $report = TestReport::factory()->create([
            'test_order_id' => $order->id,
            'generated_by' => $technician->id,
        ]);

        $this->assertInstanceOf(User::class, $report->generatedBy);
        $this->assertEquals($technician->id, $report->generatedBy->id);
    }

    // ── Media Collection Tests (T040) ────────────────────────────────────────────

    /**
     * T040: Test TestReport media collection registration (registerMediaCollections)
     */
    public function test_register_media_collections_method_exists()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);

        $this->assertTrue(method_exists($report, 'registerMediaCollections'));
    }

    public function test_report_file_media_collection_exists()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);

        // Verify the media collection is registered
        $collection = $report->getMediaCollection('report_file');

        $this->assertNotNull($collection);
        $this->assertEquals('report_file', $collection->name);
    }

    // ── Soft Delete Tests (T041) ─────────────────────────────────────────────────

    /**
     * T041: Test TestReport soft delete behavior
     */
    public function test_soft_delete_behavior()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);
        $reportId = $report->id;

        $report->delete();

        $this->assertSoftDeleted($report);
        $this->assertDatabaseHas('test_reports', [
            'id' => $reportId,
            'deleted_at' => $report->deleted_at,
        ]);
    }

    // ── Accessor Tests (T084-T085) ───────────────────────────────────────────────

    /**
     * T084: Test file_url accessor returns media URL or null
     */
    public function test_file_url_accessor_returns_null_when_no_media()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);

        $this->assertNull($report->file_url);
    }

    /**
     * T085: Test status_badge accessor returns HTML badge
     */
    public function test_status_badge_accessor_returns_html_badge_for_draft()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->draft()->create(['test_order_id' => $order->id]);

        $badge = $report->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-warning', $badge);
    }

    public function test_status_badge_accessor_returns_html_badge_for_final()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->final()->create(['test_order_id' => $order->id]);

        $badge = $report->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-success', $badge);
    }

    public function test_status_badge_accessor_returns_html_badge_for_amended()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->amended()->create(['test_order_id' => $order->id]);

        $badge = $report->status_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-info', $badge);
    }

    // ── Edge Case Tests (T098-T099) ──────────────────────────────────────────────

    /**
     * T098: Test file_url accessor with no media attached
     */
    public function test_file_url_accessor_with_no_media_attached()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create(['test_order_id' => $order->id]);

        // No media attached
        $this->assertNull($report->file_url);
    }

    /**
     * T099: Test null generated_by relationship handled gracefully
     */
    public function test_null_generated_by_relationship_handled_gracefully()
    {
        $order = TestOrder::factory()->create();
        $report = TestReport::factory()->create([
            'test_order_id' => $order->id,
            'generated_by' => null,
        ]);

        $this->assertNull($report->generatedBy);
    }
}
