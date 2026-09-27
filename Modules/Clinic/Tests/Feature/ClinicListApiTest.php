<?php

namespace Modules\Clinic\Tests\Feature;

use App\Models\User;
use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\ClinicGallery;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Tests\Unit\BaseTest;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class ClinicListApiTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => 'database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Location/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Appointment/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Clinic/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Service/database/migrations', '--force' => true]);
    }

    public function test_guest_list_filters_by_location_and_service_price_and_returns_localized_location(): void
    {
        $vendor = User::factory()->create();
        $cairo = Governorate::create(['name_ar' => 'القاهرة', 'name_en' => 'Cairo']);
        $nasrCity = GovernorateCity::create([
            'governorate_id' => $cairo->id,
            'name_ar' => 'مدينة نصر',
            'name_en' => 'Nasr City',
        ]);
        $alexandria = Governorate::create(['name_ar' => 'الإسكندرية', 'name_en' => 'Alexandria']);

        $cairoClinic = $this->clinic($vendor->id, 'qa-cairo', $cairo->id, $nasrCity->id);
        $alexClinic = $this->clinic($vendor->id, 'qa-alex', $alexandria->id, null);
        $this->service($cairoClinic->id, $vendor->id, 300);
        $this->service($alexClinic->id, $vendor->id, 900);

        $response = $this->withHeaders(['global-localization' => 'ar'])
            ->getJson('/api/get-clinic-list?governorate_id='.$cairo->id.'&city_id='.$nasrCity->id.'&is_price_max=500');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'عيادة الاختبار')
            ->assertJsonPath('data.0.state_name', 'القاهرة')
            ->assertJsonPath('data.0.city_name', 'مدينة نصر');
    }

    public function test_invalid_price_range_is_rejected_and_detail_returns_relationship_counts(): void
    {
        $vendor = User::factory()->create();
        $clinic = $this->clinic($vendor->id, 'qa-detail', null, null);
        $this->service($clinic->id, $vendor->id, 300);

        $this->getJson('/api/get-clinic-list?is_price_min=900&is_price_max=300')
            ->assertStatus(422);

        $this->getJson('/api/get-clinic-details?clinic_id='.$clinic->id)
            ->assertOk()
            ->assertJsonPath('data.total_services', 1)
            ->assertJsonPath('data.name_en', 'QA Clinic');
    }

    public function test_gallery_respects_page_and_per_page(): void
    {
        $vendor = User::factory()->create();
        $clinic = $this->clinic($vendor->id, 'qa-gallery', null, null);
        $first = ClinicGallery::create(['clinic_id' => $clinic->id, 'full_url' => '/gallery/one.png', 'status' => 1]);
        $second = ClinicGallery::create(['clinic_id' => $clinic->id, 'full_url' => '/gallery/two.png', 'status' => 1]);

        $this->getJson('/api/get-clinic-gallery?clinic_id='.$clinic->id.'&per_page=1&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonMissing(['id' => $first->id]);
    }

    private function clinic(int $vendorId, string $slug, ?int $governorateId, ?int $cityId): Clinics
    {
        return Clinics::create([
            'name' => 'QA Clinic',
            'name_ar' => 'عيادة الاختبار',
            'name_en' => 'QA Clinic',
            'email' => $slug.'@example.test',
            'slug' => $slug,
            'contact_number' => '01000000000',
            'address' => 'QA Address',
            'vendor_id' => $vendorId,
            'governorate_id' => $governorateId,
            'city_id' => $cityId,
            'status' => 1,
        ]);
    }

    private function service(int $clinicId, int $vendorId, int $charges): void
    {
        $service = ClinicsService::create([
            'name' => 'QA Service '.$charges,
            'charges' => $charges,
            'vendor_id' => $vendorId,
            'status' => 1,
        ]);

        ClinicServiceMapping::create(['clinic_id' => $clinicId, 'service_id' => $service->id]);
    }
}
