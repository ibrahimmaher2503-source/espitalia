<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuDepartment;

class IcuDepartmentTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Bilingual Accessor Tests ────────────────────────────

    public function test_name_accessor_returns_english_when_locale_is_en()
    {
        app()->setLocale('en');
        $dept = IcuDepartment::factory()->create([
            'name_en' => 'Cardiac ICU',
            'name_ar' => 'عناية القلب',
        ]);

        $this->assertEquals('Cardiac ICU', $dept->name);
    }

    public function test_name_accessor_returns_arabic_when_locale_is_ar()
    {
        app()->setLocale('ar');
        $dept = IcuDepartment::factory()->create([
            'name_en' => 'Cardiac ICU',
            'name_ar' => 'عناية القلب',
        ]);

        $this->assertEquals('عناية القلب', $dept->name);
    }

    public function test_name_accessor_falls_back_to_other_locale_when_preferred_is_null()
    {
        app()->setLocale('en');
        $dept = IcuDepartment::factory()->create([
            'name_en' => null,
            'name_ar' => 'عناية القلب',
        ]);

        $this->assertEquals('عناية القلب', $dept->name);
    }

    public function test_name_accessor_falls_back_to_english_when_arabic_is_null()
    {
        app()->setLocale('ar');
        $dept = IcuDepartment::factory()->create([
            'name_en' => 'Cardiac ICU',
            'name_ar' => null,
        ]);

        $this->assertEquals('Cardiac ICU', $dept->name);
    }

    public function test_name_accessor_returns_empty_string_when_both_null()
    {
        app()->setLocale('en');
        $dept = IcuDepartment::factory()->create([
            'name_en' => null,
            'name_ar' => null,
        ]);

        $this->assertEquals('', $dept->name);
    }

    public function test_description_accessor_returns_english_when_locale_is_en()
    {
        app()->setLocale('en');
        $dept = IcuDepartment::factory()->create([
            'description_en' => 'English description',
            'description_ar' => 'وصف عربي',
        ]);

        $this->assertEquals('English description', $dept->description);
    }

    public function test_description_accessor_returns_arabic_when_locale_is_ar()
    {
        app()->setLocale('ar');
        $dept = IcuDepartment::factory()->create([
            'description_en' => 'English description',
            'description_ar' => 'وصف عربي',
        ]);

        $this->assertEquals('وصف عربي', $dept->description);
    }

    public function test_description_accessor_falls_back_when_preferred_locale_is_null()
    {
        app()->setLocale('en');
        $dept = IcuDepartment::factory()->create([
            'description_en' => null,
            'description_ar' => 'وصف عربي',
        ]);

        $this->assertEquals('وصف عربي', $dept->description);
    }

    // ── Fillable Tests ────────────────────────────────────────

    public function test_fillable_contains_bilingual_name_fields()
    {
        $dept = new IcuDepartment;
        $fillable = $dept->getFillable();

        $this->assertContains('name_ar', $fillable);
        $this->assertContains('name_en', $fillable);
    }

    public function test_fillable_contains_bilingual_description_fields()
    {
        $dept = new IcuDepartment;
        $fillable = $dept->getFillable();

        $this->assertContains('description_ar', $fillable);
        $this->assertContains('description_en', $fillable);
    }

    public function test_fillable_does_not_contain_old_name_field()
    {
        $dept = new IcuDepartment;
        $fillable = $dept->getFillable();

        $this->assertNotContains('name', $fillable);
    }

    public function test_fillable_does_not_contain_old_description_field()
    {
        $dept = new IcuDepartment;
        $fillable = $dept->getFillable();

        $this->assertNotContains('description', $fillable);
    }

    // ── Cast Tests ─────────────────────────────────────────────

    public function test_has_ventilator_is_cast_to_boolean()
    {
        $dept = IcuDepartment::factory()->create(['has_ventilator' => 1]);

        $this->assertIsBool($dept->has_ventilator);
        $this->assertTrue($dept->has_ventilator);
    }

    public function test_has_oxygen_is_cast_to_boolean()
    {
        $dept = IcuDepartment::factory()->create(['has_oxygen' => 1]);

        $this->assertIsBool($dept->has_oxygen);
        $this->assertTrue($dept->has_oxygen);
    }

    public function test_daily_price_is_cast_to_numeric()
    {
        $dept = IcuDepartment::factory()->create(['daily_price' => '250.50']);

        $this->assertIsNumeric($dept->daily_price);
        $this->assertEquals('250.50', $dept->daily_price);
    }

    public function test_total_beds_is_cast_to_integer()
    {
        $dept = IcuDepartment::factory()->create(['total_beds' => '10']);

        $this->assertIsInt($dept->total_beds);
        $this->assertEquals(10, $dept->total_beds);
    }

    // ── Relationship Tests ────────────────────────────────────

    public function test_department_belongs_to_hospital()
    {
        $hospital = Hospital::factory()->create();
        $dept = IcuDepartment::factory()->create(['hospital_id' => $hospital->id]);

        $this->assertInstanceOf(Hospital::class, $dept->hospital);
        $this->assertEquals($hospital->id, $dept->hospital->id);
    }

    // ── Constant Tests ────────────────────────────────────────

    public function test_specialty_types_constant_defined()
    {
        $this->assertIsArray(IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('cardiac', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('neurology', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('pediatric', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('neonatal', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('burns', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('chest', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('surgical', IcuDepartment::SPECIALTY_TYPES);
        $this->assertContains('general', IcuDepartment::SPECIALTY_TYPES);
    }

    public function test_equipment_levels_constant_defined()
    {
        $this->assertIsArray(IcuDepartment::EQUIPMENT_LEVELS);
        $this->assertContains('basic', IcuDepartment::EQUIPMENT_LEVELS);
        $this->assertContains('advanced', IcuDepartment::EQUIPMENT_LEVELS);
        $this->assertContains('full', IcuDepartment::EQUIPMENT_LEVELS);
    }

    // ── Scope Tests ────────────────────────────────────────────

    public function test_active_scope()
    {
        $activeDept = IcuDepartment::factory()->create(['status' => 1]);
        $inactiveDept = IcuDepartment::factory()->create(['status' => 0]);

        $results = IcuDepartment::active()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($activeDept));
        $this->assertFalse($results->contains($inactiveDept));
    }

    public function test_available_scope()
    {
        $availableDept = IcuDepartment::factory()->create(['available_beds' => 5]);
        $fullDept = IcuDepartment::factory()->full()->create();

        $results = IcuDepartment::available()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($availableDept));
        $this->assertFalse($results->contains($fullDept));
    }

    public function test_with_ventilator_scope()
    {
        $withVent = IcuDepartment::factory()->withVentilator()->create();
        $withoutVent = IcuDepartment::factory()->create(['has_ventilator' => false]);

        $results = IcuDepartment::withVentilator()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($withVent));
        $this->assertFalse($results->contains($withoutVent));
    }

    public function test_by_specialty_scope()
    {
        $cardiacDept = IcuDepartment::factory()->cardiac()->create();
        $neuroDept = IcuDepartment::factory()->neurology()->create();

        $results = IcuDepartment::bySpecialty('cardiac')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($cardiacDept));
        $this->assertFalse($results->contains($neuroDept));
    }

    // ── Soft Delete Tests ─────────────────────────────────────

    public function test_soft_delete_behavior()
    {
        $dept = IcuDepartment::factory()->create();
        $deptId = $dept->id;

        $dept->delete();

        $this->assertSoftDeleted($dept);
        $this->assertDatabaseHas('icu_departments', [
            'id' => $deptId,
            'deleted_at' => $dept->deleted_at,
        ]);
    }
}
