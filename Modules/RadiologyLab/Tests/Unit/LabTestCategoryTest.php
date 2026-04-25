<?php

namespace Modules\RadiologyLab\Tests\Unit;

use Modules\RadiologyLab\Models\LabTest;
use Modules\RadiologyLab\Models\LabTestCategory;

class LabTestCategoryTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_status_is_cast_to_boolean()
    {
        $category = LabTestCategory::factory()->create(['status' => 1]);

        $this->assertIsBool($category->status);
        $this->assertTrue($category->status);
    }

    public function test_display_order_is_cast_to_integer()
    {
        $category = LabTestCategory::factory()->create(['display_order' => '5']);

        $this->assertIsInt($category->display_order);
        $this->assertEquals(5, $category->display_order);
    }

    public function test_category_has_many_lab_tests()
    {
        $category = LabTestCategory::factory()->create();
        $labTest1 = LabTest::factory()->create(['category_id' => $category->id]);
        $labTest2 = LabTest::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(LabTest::class, $category->labTests()->getRelated());
        $this->assertCount(2, $category->labTests);
        $this->assertTrue($category->labTests->contains($labTest1));
        $this->assertTrue($category->labTests->contains($labTest2));
    }

    public function test_slug_is_auto_generated()
    {
        $category = LabTestCategory::factory()->create(['name' => 'Blood Tests']);

        $this->assertNotNull($category->slug);
        $this->assertEquals('blood-tests', $category->slug);
    }

    public function test_soft_delete_behavior()
    {
        $category = LabTestCategory::factory()->create();
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted($category);
        $this->assertDatabaseHas('lab_test_categories', [
            'id' => $categoryId,
            'deleted_at' => $category->deleted_at,
        ]);
    }

    public function test_active_scope_filters_categories_with_status_1()
    {
        $activeCategory = LabTestCategory::factory()->active()->create();
        $inactiveCategory = LabTestCategory::factory()->inactive()->create();

        $results = LabTestCategory::active()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($activeCategory));
        $this->assertFalse($results->contains($inactiveCategory));
    }

    public function test_ordered_scope_sorts_by_display_order_ascending()
    {
        $category1 = LabTestCategory::factory()->create(['display_order' => 3]);
        $category2 = LabTestCategory::factory()->create(['display_order' => 1]);
        $category3 = LabTestCategory::factory()->create(['display_order' => 2]);

        $results = LabTestCategory::ordered()->get();

        $this->assertCount(3, $results);
        $this->assertEquals($category2->id, $results->first()->id);
        $this->assertEquals($category3->id, $results->get(1)->id);
        $this->assertEquals($category1->id, $results->get(2)->id);
    }

    public function test_category_with_no_associated_lab_tests()
    {
        $category = LabTestCategory::factory()->create();

        $this->assertCount(0, $category->labTests);
    }

    public function test_ordered_scope_with_duplicate_display_order_values()
    {
        $category1 = LabTestCategory::factory()->create(['display_order' => 1]);
        $category2 = LabTestCategory::factory()->create(['display_order' => 1]);

        $results = LabTestCategory::ordered()->get();

        $this->assertCount(2, $results);
        $this->assertContains($category1->id, $results->pluck('id')->toArray());
        $this->assertContains($category2->id, $results->pluck('id')->toArray());
    }
}
