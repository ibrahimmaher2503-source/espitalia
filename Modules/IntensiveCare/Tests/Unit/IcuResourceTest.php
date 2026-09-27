<?php

namespace Modules\IntensiveCare\Tests\Unit;

use Illuminate\Http\Request;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuAdmissionRequest;
use Modules\IntensiveCare\Models\IcuDepartment;
use Modules\IntensiveCare\Transformers\IcuAdmissionRequestResource;
use Modules\IntensiveCare\Transformers\IcuDepartmentResource;

class IcuResourceTest extends BaseTest
{
    public function test_admission_resource_exposes_localized_and_bilingual_nested_names(): void
    {
        app()->setLocale('ar');
        $hospital = Hospital::factory()->create([
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ]);
        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
            'name_ar' => 'العناية المركزة',
            'name_en' => 'Intensive Care',
        ]);
        $admission = IcuAdmissionRequest::factory()->create([
            'hospital_id' => $hospital->id,
            'icu_department_id' => $department->id,
        ])->load(['hospital', 'icuDepartment']);

        $data = (new IcuAdmissionRequestResource($admission))->resolve(new Request);

        $this->assertSame([
            'id' => $hospital->id,
            'name' => 'مستشفى القاهرة',
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ], $data['hospital']);
        $this->assertSame('العناية المركزة', $data['department']['name']);
        $this->assertSame('العناية المركزة', $data['department']['name_ar']);
        $this->assertSame('Intensive Care', $data['department']['name_en']);
    }

    public function test_department_resource_exposes_localized_and_bilingual_nested_hospital_name(): void
    {
        app()->setLocale('en');
        $hospital = Hospital::factory()->create([
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ]);
        $department = IcuDepartment::factory()->create([
            'hospital_id' => $hospital->id,
        ])->load('hospital');

        $data = (new IcuDepartmentResource($department))->resolve(new Request);

        $this->assertSame([
            'id' => $hospital->id,
            'name' => 'Cairo Hospital',
            'name_ar' => 'مستشفى القاهرة',
            'name_en' => 'Cairo Hospital',
        ], $data['hospital']);
    }
}
