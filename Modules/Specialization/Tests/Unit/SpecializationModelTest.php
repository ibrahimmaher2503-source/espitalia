<?php

namespace Modules\Specialization\Tests\Unit;

use Modules\Specialization\Models\Specialization;

class SpecializationModelTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->runCoreMigrations();
        $this->runSpecializationMigrations();
    }

    public function test_bilingual_name_returns_english_by_default(): void
    {
        $spec = Specialization::create([
            'name_ar' => 'طب عام',
            'name_en' => 'General Medicine',
            'status' => 1,
        ]);

        app()->setLocale('en');
        $this->assertEquals('General Medicine', $spec->name);
    }

    public function test_bilingual_name_returns_arabic_when_locale_is_ar(): void
    {
        $spec = Specialization::create([
            'name_ar' => 'طب عام',
            'name_en' => 'General Medicine',
            'status' => 1,
        ]);

        app()->setLocale('ar');
        $this->assertEquals('طب عام', $spec->name);
    }

    public function test_bilingual_name_falls_back_when_preferred_locale_empty(): void
    {
        $spec = Specialization::create([
            'name_ar' => '',
            'name_en' => 'General Medicine',
            'status' => 1,
        ]);

        app()->setLocale('ar');
        $this->assertEquals('General Medicine', $spec->name);
    }

    public function test_bilingual_description_accessor(): void
    {
        $spec = Specialization::create([
            'name_en' => 'Cardiology',
            'description_ar' => 'وصف',
            'description_en' => 'Description',
            'status' => 1,
        ]);

        app()->setLocale('en');
        $this->assertEquals('Description', $spec->description);

        app()->setLocale('ar');
        $this->assertEquals('وصف', $spec->description);
    }

    public function test_parent_child_relationship(): void
    {
        $parent = Specialization::create(['name_en' => 'Surgery', 'status' => 1]);
        $child = Specialization::create(['name_en' => 'Cardiac Surgery', 'parent_id' => $parent->id, 'status' => 1]);

        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertTrue($parent->children->contains($child));
    }

    public function test_scope_active_filters_inactive(): void
    {
        Specialization::query()->forceDelete();
        Specialization::create(['name_en' => 'Active', 'status' => 1]);
        Specialization::create(['name_en' => 'Inactive', 'status' => 0]);

        $active = Specialization::active()->get();
        $this->assertCount(1, $active);
        $this->assertEquals('Active', $active->first()->name_en);
    }

    public function test_scope_top_level_filters_children(): void
    {
        Specialization::query()->forceDelete();
        $parent = Specialization::create(['name_en' => 'Parent', 'status' => 1]);
        Specialization::create(['name_en' => 'Child', 'parent_id' => $parent->id, 'status' => 1]);

        $topLevel = Specialization::topLevel()->get();
        $this->assertCount(1, $topLevel);
        $this->assertEquals('Parent', $topLevel->first()->name_en);
    }

    public function test_slug_generated_from_name_en_on_create(): void
    {
        $spec = Specialization::create(['name_en' => 'General Medicine', 'status' => 1]);
        $this->assertEquals('general-medicine', $spec->slug);
    }

    public function test_slug_updated_when_name_en_changes(): void
    {
        $spec = Specialization::create(['name_en' => 'General Medicine', 'status' => 1]);
        $spec->update(['name_en' => 'Internal Medicine']);
        $this->assertEquals('internal-medicine', $spec->fresh()->slug);
    }

    public function test_soft_delete_works(): void
    {
        $spec = Specialization::create(['name_en' => 'To Delete', 'status' => 1]);
        $spec->delete();

        $this->assertSoftDeleted('specializations', ['id' => $spec->id]);
        $this->assertNull(Specialization::find($spec->id));
        $this->assertNotNull(Specialization::withTrashed()->find($spec->id));
    }
}
