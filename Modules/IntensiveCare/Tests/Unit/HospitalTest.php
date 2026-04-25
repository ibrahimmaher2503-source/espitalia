<?php

namespace Modules\IntensiveCare\Tests\Unit;

use App\Models\User;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuCommission;
use Modules\IntensiveCare\Models\IcuDepartment;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class HospitalTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // ── Bilingual Accessor Tests ─────────────────────────────────

    public function test_name_accessor_returns_arabic_when_locale_is_ar()
    {
        $hospital = Hospital::factory()->create([
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('مستشفى القاهرة', $hospital->name);
    }

    public function test_name_accessor_returns_english_when_locale_is_en()
    {
        $hospital = Hospital::factory()->create([
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ]);

        app()->setLocale('en');

        $this->assertEquals('Cairo Hospital', $hospital->name);
    }

    public function test_name_accessor_falls_back_to_english_when_arabic_is_empty()
    {
        $hospital = Hospital::factory()->create([
            'name_ar' => '',
            'name_en' => 'Cairo Hospital',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('Cairo Hospital', $hospital->name);
    }

    public function test_name_accessor_falls_back_to_arabic_when_english_is_empty()
    {
        $hospital = Hospital::factory()->create([
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => '',
        ]);

        app()->setLocale('en');

        $this->assertEquals('مستشفى القاهرة', $hospital->name);
    }

    public function test_name_accessor_returns_empty_string_when_both_empty()
    {
        $hospital = Hospital::factory()->create([
            'name_ar' => '',
            'name_en' => '',
        ]);

        app()->setLocale('en');

        $this->assertEquals('', $hospital->name);
    }

    public function test_description_accessor_returns_correct_locale()
    {
        $hospital = Hospital::factory()->create([
            'description_ar' => 'وصف بالعربية',
            'description_en' => 'English description',
        ]);

        app()->setLocale('ar');
        $this->assertEquals('وصف بالعربية', $hospital->description);

        app()->setLocale('en');
        $this->assertEquals('English description', $hospital->description);
    }

    public function test_description_accessor_falls_back_when_preferred_is_empty()
    {
        $hospital = Hospital::factory()->create([
            'description_ar' => '',
            'description_en' => 'English description',
        ]);

        app()->setLocale('ar');

        $this->assertEquals('English description', $hospital->description);
    }

    public function test_address_accessor_returns_correct_locale()
    {
        $hospital = Hospital::factory()->create([
            'address_ar' => 'شارع النيل',
            'address_en' => 'Nile Street',
        ]);

        app()->setLocale('ar');
        $this->assertEquals('شارع النيل', $hospital->address);

        app()->setLocale('en');
        $this->assertEquals('Nile Street', $hospital->address);
    }

    public function test_address_accessor_falls_back_when_preferred_is_empty()
    {
        $hospital = Hospital::factory()->create([
            'address_ar' => 'شارع النيل',
            'address_en' => '',
        ]);

        app()->setLocale('en');

        $this->assertEquals('شارع النيل', $hospital->address);
    }

    // ── Relationship Tests ───────────────────────────────────────

    public function test_governorate_relationship_returns_correct_model()
    {
        $governorate = Governorate::create([
            'name_ar' => 'القاهرة',
            'name_en' => 'Cairo',
        ]);

        $hospital = Hospital::factory()->create([
            'governorate_id' => $governorate->id,
        ]);

        $this->assertInstanceOf(Governorate::class, $hospital->governorate);
        $this->assertEquals($governorate->id, $hospital->governorate->id);
        $this->assertEquals('القاهرة', $hospital->governorate->name_ar);
    }

    public function test_city_relationship_returns_correct_model()
    {
        $governorate = Governorate::create([
            'name_ar' => 'القاهرة',
            'name_en' => 'Cairo',
        ]);

        $city = GovernorateCity::create([
            'governorate_id' => $governorate->id,
            'name_ar' => 'مدينة نصر',
            'name_en' => 'Nasr City',
        ]);

        $hospital = Hospital::factory()->create([
            'governorate_id' => $governorate->id,
            'city_id' => $city->id,
        ]);

        $this->assertInstanceOf(GovernorateCity::class, $hospital->governorateCity);
        $this->assertEquals($city->id, $hospital->governorateCity->id);
        $this->assertEquals('مدينة نصر', $hospital->governorateCity->name_ar);
    }

    public function test_commission_relationship_returns_correct_model()
    {
        $commission = IcuCommission::create([
            'title' => 'Standard Commission',
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
            'status' => true,
        ]);

        $hospital = Hospital::factory()->create([
            'commission_id' => $commission->id,
        ]);

        $this->assertInstanceOf(IcuCommission::class, $hospital->commission);
        $this->assertEquals($commission->id, $hospital->commission->id);
        $this->assertEquals('Standard Commission', $hospital->commission->title);
    }

    public function test_hospital_has_many_icu_departments()
    {
        $hospital = Hospital::factory()->create();
        $dept1 = IcuDepartment::factory()->create(['hospital_id' => $hospital->id]);
        $dept2 = IcuDepartment::factory()->create(['hospital_id' => $hospital->id]);

        $this->assertInstanceOf(IcuDepartment::class, $hospital->icuDepartments()->getRelated());
        $this->assertCount(2, $hospital->icuDepartments);
        $this->assertTrue($hospital->icuDepartments->contains($dept1));
        $this->assertTrue($hospital->icuDepartments->contains($dept2));
    }

    public function test_hospital_has_many_admission_requests()
    {
        $hospital = Hospital::factory()->create();
        $request1 = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital->id]);
        $request2 = IcuAdmissionRequest::factory()->create(['hospital_id' => $hospital->id]);

        $this->assertInstanceOf(IcuAdmissionRequest::class, $hospital->admissionRequests()->getRelated());
        $this->assertCount(2, $hospital->admissionRequests);
        $this->assertTrue($hospital->admissionRequests->contains($request1));
        $this->assertTrue($hospital->admissionRequests->contains($request2));
    }

    public function test_hospital_belongs_to_vendor()
    {
        $vendor = User::factory()->create();
        $hospital = Hospital::factory()->create(['vendor_id' => $vendor->id]);

        $this->assertInstanceOf(User::class, $hospital->vendor);
        $this->assertEquals($vendor->id, $hospital->vendor->id);
    }

    // ── Fillable Tests ───────────────────────────────────────────

    public function test_fillable_contains_bilingual_fields()
    {
        $hospital = new Hospital;
        $fillable = $hospital->getFillable();

        $this->assertContains('name_ar', $fillable);
        $this->assertContains('name_en', $fillable);
        $this->assertContains('description_ar', $fillable);
        $this->assertContains('description_en', $fillable);
        $this->assertContains('address_ar', $fillable);
        $this->assertContains('address_en', $fillable);
        $this->assertContains('governorate_id', $fillable);
        $this->assertContains('city_id', $fillable);
        $this->assertContains('commission_id', $fillable);
    }

    public function test_fillable_does_not_contain_old_monolingual_fields()
    {
        $hospital = new Hospital;
        $fillable = $hospital->getFillable();

        // Old monolingual columns were dropped in migration
        $this->assertNotContains('name', $fillable);
        $this->assertNotContains('description', $fillable);
        $this->assertNotContains('address', $fillable);
    }

    // ── Existing Tests (preserved) ──────────────────────────────

    public function test_status_is_cast_to_boolean()
    {
        $hospital = Hospital::factory()->create(['status' => 1]);

        $this->assertIsBool($hospital->status);
        $this->assertTrue($hospital->status);
    }

    public function test_accepted_insurance_is_cast_to_array()
    {
        $insurance = ['Insurance A', 'Insurance B'];
        $hospital = Hospital::factory()->create(['accepted_insurance' => $insurance]);

        $this->assertIsArray($hospital->accepted_insurance);
        $this->assertCount(2, $hospital->accepted_insurance);
        $this->assertContains('Insurance A', $hospital->accepted_insurance);
        $this->assertContains('Insurance B', $hospital->accepted_insurance);
    }

    public function test_rating_is_cast_to_numeric()
    {
        $hospital = Hospital::factory()->create(['rating' => 4.75]);

        $this->assertIsNumeric($hospital->rating);
        $this->assertEquals('4.75', $hospital->rating);
    }

    public function test_soft_delete_behavior()
    {
        $hospital = Hospital::factory()->create();
        $hospitalId = $hospital->id;

        $hospital->delete();

        $this->assertSoftDeleted($hospital);
        $this->assertDatabaseHas('hospitals', [
            'id' => $hospitalId,
            'deleted_at' => $hospital->deleted_at,
        ]);
    }

    public function test_active_scope_filters()
    {
        $activeHospital = Hospital::factory()->active()->create();
        $inactiveHospital = Hospital::factory()->inactive()->create();

        $results = Hospital::active()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($activeHospital));
        $this->assertFalse($results->contains($inactiveHospital));
    }

    public function test_hospital_types_constant_defined()
    {
        $this->assertIsArray(Hospital::HOSPITAL_TYPES);
        $this->assertContains('government', Hospital::HOSPITAL_TYPES);
        $this->assertContains('private', Hospital::HOSPITAL_TYPES);
        $this->assertContains('military', Hospital::HOSPITAL_TYPES);
        $this->assertContains('university', Hospital::HOSPITAL_TYPES);
    }

    public function test_register_media_collections()
    {
        $hospital = Hospital::factory()->create();

        $collections = $hospital->getRegisteredMediaCollections();
        $collectionNames = $collections->pluck('name')->toArray();

        $this->assertContains('logo', $collectionNames);
        $this->assertContains('gallery', $collectionNames);
    }
}
