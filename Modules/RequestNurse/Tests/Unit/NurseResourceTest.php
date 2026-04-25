<?php

namespace Modules\RequestNurse\Tests\Unit;

use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Transformers\NurseResource;

class NurseResourceTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_nurse_resource_contains_all_expected_keys()
    {
        $nurse = Nurse::factory()->create();
        $nurse->load('user');

        $resource = new NurseResource($nurse);
        $array = $resource->toArray(request());

        $expectedKeys = [
            'id',
            'nurse_id',
            'name',
            'first_name',
            'last_name',
            'email',
            'mobile',
            'specialization',
            'experience',
            'about',
            'hourly_rate',
            'availability_status',
            'service_area',
            'profile_image',
            'status',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    public function test_nurse_resource_maps_user_data_correctly()
    {
        $nurse = Nurse::factory()->create();
        $nurse->load('user');

        $resource = new NurseResource($nurse);
        $array = $resource->toArray(request());

        $this->assertEquals($nurse->user->first_name, $array['first_name']);
        $this->assertEquals($nurse->user->last_name, $array['last_name']);
        $this->assertEquals($nurse->user->email, $array['email']);
        $this->assertEquals($nurse->user->mobile, $array['mobile']);
        $this->assertEquals(
            $nurse->user->first_name.' '.$nurse->user->last_name,
            $array['name']
        );
    }

    public function test_nurse_resource_profile_image_is_null_when_no_media()
    {
        $nurse = Nurse::factory()->create();
        $nurse->load('user');

        $resource = new NurseResource($nurse);
        $array = $resource->toArray(request());

        $this->assertNull($array['profile_image']);
    }

    public function test_nurse_resource_maps_nurse_fields_correctly()
    {
        $nurse = Nurse::factory()->create([
            'specialization_en' => 'Pediatric',
            'specialization_ar' => 'أطفال',
            'experience_en' => '5 years',
            'experience_ar' => '5 سنوات',
            'about_en' => 'Experienced nurse',
            'about_ar' => 'ممرضة ذات خبرة',
            'hourly_rate' => 75.50,
            'availability_status' => 'available',
            'service_area_en' => 'New York',
            'service_area_ar' => 'نيويورك',
        ]);
        $nurse->load('user');

        $resource = new NurseResource($nurse);
        $array = $resource->toArray(request());

        $this->assertEquals('Pediatric', $array['specialization']);
        $this->assertEquals('5 years', $array['experience']);
        $this->assertEquals('Experienced nurse', $array['about']);
        $this->assertEquals(75.50, $array['hourly_rate']);
        $this->assertEquals('available', $array['availability_status']);
        $this->assertEquals('New York', $array['service_area']);
    }
}
