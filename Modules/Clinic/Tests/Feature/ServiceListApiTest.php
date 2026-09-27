<?php

namespace Modules\Clinic\Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsCategory;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\SystemService;
use Modules\Clinic\Tests\Unit\BaseTest;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class ServiceListApiTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Location/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Appointment/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Clinic/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Service/database/migrations', '--force' => true]);
        Schema::table('clinics_services', function (Blueprint $table): void {
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
        });
    }

    public function test_public_and_patient_lists_only_return_active_localized_services_with_each_price_bound(): void
    {
        [$category, $governorate, $city, $clinic] = $this->fixtures();
        $active = $this->service($category->id, $clinic->vendor_id, 300, 1, 'خدمة نشطة', 'Active service');
        $inactive = $this->service($category->id, $clinic->vendor_id, 900, 0, 'خدمة مخفية', 'Hidden service');
        ClinicServiceMapping::create(['clinic_id' => $clinic->id, 'service_id' => $active->id]);
        ClinicServiceMapping::create(['clinic_id' => $clinic->id, 'service_id' => $inactive->id]);

        $this->withHeaders(['global-localization' => 'ar'])->getJson('/api/get-service-list?governorate_id='.$governorate->id.'&city_id='.$city->id.'&is_price_max=500')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'خدمة نشطة')
            ->assertJsonPath('data.0.system_service_id', null);

        $patient = User::factory()->create(['user_type' => 'user']);
        $this->actingAs($patient)->getJson('/api/get-service-list?is_price_min=800')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_service_detail_has_relationships_and_invalid_filters_are_json_validation_errors(): void
    {
        [$category, $governorate, $city, $clinic] = $this->fixtures();
        $systemService = SystemService::create(['name' => 'QA system service', 'category_id' => $category->id, 'status' => 1]);
        $service = $this->service($category->id, $clinic->vendor_id, 300, 1, 'خدمة اختبار', 'QA service');
        $service->update(['system_service_id' => $systemService->id]);
        ClinicServiceMapping::create(['clinic_id' => $clinic->id, 'service_id' => $service->id]);

        $this->getJson('/api/get-service-list?system_service_id='.$systemService->id)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.system_service_id', $systemService->id);
        $this->getJson('/api/get-service-details?service_id='.$service->id)
            ->assertOk()->assertJsonPath('data.id', $service->id)
            ->assertJsonPath('data.name_en', 'QA service')->assertJsonCount(1, 'data.clinics');
        $this->getJson('/api/get-service-list?is_price_min=900&is_price_max=300')->assertStatus(422);
        $this->getJson('/api/get-service-list?city_id=999999')->assertStatus(422);
    }

    private function fixtures(): array
    {
        $vendor = User::factory()->create(['user_type' => 'admin']);
        $category = ClinicsCategory::create(['name' => 'QA', 'name_ar' => 'اختبار', 'name_en' => 'QA', 'status' => 1]);
        $governorate = Governorate::create(['name_ar' => 'القاهرة', 'name_en' => 'Cairo']);
        $city = GovernorateCity::create(['governorate_id' => $governorate->id, 'name_ar' => 'مدينة نصر', 'name_en' => 'Nasr City']);
        $clinic = Clinics::create(['name' => 'QA Clinic', 'name_ar' => 'عيادة اختبار', 'name_en' => 'QA Clinic', 'email' => 'service-qa@example.test', 'slug' => 'service-qa', 'contact_number' => '01000000000', 'address' => 'QA', 'vendor_id' => $vendor->id, 'governorate_id' => $governorate->id, 'city_id' => $city->id, 'status' => 1]);
        return [$category, $governorate, $city, $clinic];
    }

    private function service(int $categoryId, int $vendorId, int $price, int $status, string $nameAr, string $nameEn): ClinicsService
    {
        return ClinicsService::create(['name' => $nameEn, 'name_ar' => $nameAr, 'name_en' => $nameEn, 'description_ar' => 'وصف', 'description_en' => 'Description', 'category_id' => $categoryId, 'vendor_id' => $vendorId, 'charges' => $price, 'status' => $status]);
    }
}
