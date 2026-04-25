<?php

namespace Modules\RadiologyLab\Tests\Unit;

use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;
use Modules\RadiologyLab\Models\TestOrderItem;

class LabTestTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // Basic Model Tests

    public function test_status_is_cast_to_boolean()
    {
        $labTest = LabTest::factory()->create(['status' => 1]);

        $this->assertIsBool($labTest->status);
        $this->assertTrue($labTest->status);
    }

    public function test_default_price_is_cast_to_decimal()
    {
        $labTest = LabTest::factory()->create(['default_price' => '150.50']);

        $this->assertIsNumeric($labTest->default_price);
        $this->assertEquals('150.50', $labTest->default_price);
    }

    public function test_sample_types_constant_defined()
    {
        $this->assertIsArray(LabTest::SAMPLE_TYPES);
        $this->assertContains('blood', LabTest::SAMPLE_TYPES);
        $this->assertContains('urine', LabTest::SAMPLE_TYPES);
        $this->assertContains('stool', LabTest::SAMPLE_TYPES);
        $this->assertContains('tissue', LabTest::SAMPLE_TYPES);
        $this->assertContains('imaging', LabTest::SAMPLE_TYPES);
        $this->assertContains('swab', LabTest::SAMPLE_TYPES);
        $this->assertContains('other', LabTest::SAMPLE_TYPES);
    }

    public function test_departments_constant_defined()
    {
        $this->assertIsArray(LabTest::DEPARTMENTS);
        $this->assertContains('laboratory', LabTest::DEPARTMENTS);
        $this->assertContains('radiology', LabTest::DEPARTMENTS);
    }

    // Relationship Tests

    public function test_lab_test_belongs_to_category()
    {
        $category = LabTestCategory::factory()->create();
        $labTest = LabTest::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(LabTestCategory::class, $labTest->category);
        $this->assertEquals($category->id, $labTest->category->id);
    }

    public function test_lab_test_has_many_order_items()
    {
        $labTest = LabTest::factory()->create();
        $orderItem1 = TestOrderItem::factory()->create(['lab_test_id' => $labTest->id]);
        $orderItem2 = TestOrderItem::factory()->create(['lab_test_id' => $labTest->id]);

        $this->assertCount(2, $labTest->orderItems);
        $this->assertTrue($labTest->orderItems->contains($orderItem1));
        $this->assertTrue($labTest->orderItems->contains($orderItem2));
    }

    // Slug Tests

    public function test_slug_is_auto_generated()
    {
        $labTest = LabTest::factory()->create(['name' => 'Complete Blood Count']);

        $this->assertNotNull($labTest->slug);
        $this->assertEquals('complete-blood-count', $labTest->slug);
    }

    // Soft Delete Tests

    public function test_soft_delete_behavior()
    {
        $labTest = LabTest::factory()->create();
        $testId = $labTest->id;

        $labTest->delete();

        $this->assertSoftDeleted($labTest);
        $this->assertDatabaseHas('lab_tests', [
            'id' => $testId,
            'deleted_at' => $labTest->deleted_at,
        ]);
    }

    // Scope Tests

    public function test_active_scope_filters_tests_with_status_1()
    {
        $activeTest = LabTest::factory()->active()->create();
        $inactiveTest = LabTest::factory()->inactive()->create();

        $results = LabTest::active()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($activeTest));
        $this->assertFalse($results->contains($inactiveTest));
    }

    public function test_laboratory_scope_filters_department_laboratory()
    {
        $labTest1 = LabTest::factory()->laboratory()->create();
        $labTest2 = LabTest::factory()->radiology()->create();

        $results = LabTest::laboratory()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($labTest1));
        $this->assertFalse($results->contains($labTest2));
    }

    public function test_radiology_scope_filters_department_radiology()
    {
        $labTest1 = LabTest::factory()->radiology()->create();
        $labTest2 = LabTest::factory()->laboratory()->create();

        $results = LabTest::radiology()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($labTest1));
        $this->assertFalse($results->contains($labTest2));
    }

    // Accessor Tests

    public function test_formatted_price_accessor_formats_to_2_decimal_places()
    {
        $labTest = LabTest::factory()->create(['default_price' => 150.5]);

        $this->assertEquals('150.50', $labTest->formatted_price);
    }

    public function test_formatted_price_accessor_with_various_decimal_places()
    {
        $labTest1 = LabTest::factory()->create(['default_price' => 100]);
        $labTest2 = LabTest::factory()->create(['default_price' => 99.999]);
        $labTest3 = LabTest::factory()->create(['default_price' => 123.456]);

        $this->assertEquals('100.00', $labTest1->formatted_price);
        $this->assertEquals('100.00', $labTest2->formatted_price);
        $this->assertEquals('123.46', $labTest3->formatted_price);
    }

    public function test_department_badge_accessor_returns_html_badge()
    {
        $labTest1 = LabTest::factory()->laboratory()->create();
        $labTest2 = LabTest::factory()->radiology()->create();

        $badge1 = $labTest1->department_badge;
        $badge2 = $labTest2->department_badge;

        $this->assertStringContainsString('badge', $badge1);
        $this->assertStringContainsString('badge', $badge2);
        $this->assertStringContainsString('bg-info', $badge1);
        $this->assertStringContainsString('bg-warning', $badge2);
    }

    public function test_sample_type_label_accessor_returns_translated_label()
    {
        $labTest = LabTest::factory()->create(['sample_type' => 'blood']);

        $label = $labTest->sample_type_label;
        $this->assertNotEmpty($label);
        $this->assertIsString($label);
        // Label should be translated (case-insensitive contains "blood")
        $this->assertTrue(
            stripos($label, 'blood') !== false,
            "Expected label to contain 'blood' (case-insensitive), got: {$label}"
        );
    }

    // Edge Case Tests

    public function test_null_sample_type_returns_safe_default()
    {
        $labTest = LabTest::factory()->create(['sample_type' => null]);

        $this->assertEquals('-', $labTest->sample_type_label);
    }

    public function test_invalid_department_value_in_department_badge_accessor()
    {
        $labTest = LabTest::factory()->create(['department' => 'invalid']);

        $badge = $labTest->department_badge;

        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('invalid', $badge);
        $this->assertStringContainsString('bg-secondary', $badge);
    }

    public function test_deleted_lab_test_relationship_returns_null()
    {
        $labTest = LabTest::factory()->create();
        $orderItem = TestOrderItem::factory()->create(['lab_test_id' => $labTest->id]);

        $labTest->delete();

        $this->assertNull($orderItem->fresh()->labTest);
    }
}
