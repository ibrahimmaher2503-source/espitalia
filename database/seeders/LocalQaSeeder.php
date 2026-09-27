<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Pharmacy\Models\PharmacyCategory;
use Modules\Clinic\Models\ClinicGallery;
use Modules\Clinic\Models\ClinicServiceMapping;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

/**
 * Disposable fixtures for local patient-app QA. Never run this against production.
 */
class LocalQaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Local QA fixtures are disabled outside local/testing.');
            return;
        }

        config(['app.env' => 'local']);

        // A disposable QA database can already contain the base roles after a
        // failed/interrupted run. Replaying AuthTableSeeder against it is unsafe.
        $seeders = [];
        if (! Role::where('name', 'user')->exists()) {
            $seeders = [
                AuthTableSeeder::class,
                SettingSeeder::class,
                BodychartSettingSeeder::class,
                IncidenceSeeder::class,
                \Modules\Constant\database\seeders\ConstantDatabaseSeeder::class,
                \Modules\Service\database\seeders\SystemServiceCategorySeeder::class,
                \Modules\Location\database\seeders\LocationDatabaseSeeder::class,
                \Modules\Clinic\database\seeders\ClinicDatabaseSeeder::class,
                \Modules\Commission\database\seeders\CommissionDatabaseSeeder::class,
                \Modules\Currency\database\seeders\CurrencyDatabaseSeeder::class,
                \Modules\Appointment\database\seeders\AppointmentDatabaseSeeder::class,
                \Modules\RadiologyLab\database\seeders\RadiologyLabDatabaseSeeder::class,
                \Modules\IntensiveCare\database\seeders\IntensiveCareDatabaseSeeder::class,
                \Modules\RequestNurse\database\seeders\RequestNursePermissionSeeder::class,
                \Modules\RequestNurse\database\seeders\RequestNurseDatabaseSeeder::class,
            ];
        }
        if (! PharmacyCategory::exists()) {
            $seeders[] = \Modules\Pharmacy\Database\Seeders\PharmacyDatabaseSeeder::class;
        }
        if ($seeders !== []) {
            $this->call($seeders);
        }

        $patient = User::updateOrCreate(
            ['email' => 'qa.patient@espitalia.local'],
            [
                'first_name' => 'QA',
                'last_name' => 'Patient',
                'password' => Hash::make('QaPatient2026'),
                'mobile' => '01000000001',
                'user_type' => 'user',
                'gender' => 'male',
                'email_verified_at' => now(),
            ]
        );

        if (! $patient->hasRole('user')) {
            $patient->assignRole('user');
        }
        $clinicOwner = User::whereIn('user_type', ['admin', 'demo_admin'])->first() ?? $patient;

        $cairo = Governorate::firstOrCreate(
            ['name_en' => 'Cairo'],
            ['name_ar' => 'القاهرة'],
        );
        $nasrCity = GovernorateCity::firstOrCreate(
            ['governorate_id' => $cairo->id, 'name_en' => 'Nasr City'],
            ['name_ar' => 'مدينة نصر'],
        );
        $alexandria = Governorate::firstOrCreate(
            ['name_en' => 'Alexandria'],
            ['name_ar' => 'الإسكندرية'],
        );
        $smouha = GovernorateCity::firstOrCreate(
            ['governorate_id' => $alexandria->id, 'name_en' => 'Smouha'],
            ['name_ar' => 'سموحة'],
        );

        $clinic = Clinics::updateOrCreate(
            ['slug' => 'qa-cairo-clinic'],
            [
                'name' => 'QA Cairo Clinic',
                'name_ar' => 'عيادة القاهرة التجريبية',
                'name_en' => 'QA Cairo Clinic',
                'email' => 'qa-cairo-clinic@espitalia.local',
                'description' => 'Local Clinics QA fixture.',
                'description_ar' => 'بيانات اختبار محلية للعيادات.',
                'description_en' => 'Local Clinics QA fixture.',
                'address' => 'QA Tower, Nasr City',
                'contact_number' => '01000000011',
                'latitude' => '30.0626',
                'longitude' => '31.3295',
                'vendor_id' => $clinicOwner->id,
                'governorate_id' => $cairo->id,
                'city_id' => $nasrCity->id,
                'status' => 1,
            ],
        );
        $otherClinic = Clinics::updateOrCreate(
            ['slug' => 'qa-alexandria-clinic'],
            [
                'name' => 'QA Alexandria Clinic',
                'name_ar' => 'عيادة الإسكندرية التجريبية',
                'name_en' => 'QA Alexandria Clinic',
                'email' => 'qa-alexandria-clinic@espitalia.local',
                'description' => 'Second local Clinics QA fixture.',
                'description_ar' => 'بيانات اختبار محلية ثانية للعيادات.',
                'description_en' => 'Second local Clinics QA fixture.',
                'address' => 'QA Avenue, Smouha',
                'contact_number' => '01000000012',
                'latitude' => '31.2058',
                'longitude' => '29.9245',
                'vendor_id' => $clinicOwner->id,
                'governorate_id' => $alexandria->id,
                'city_id' => $smouha->id,
                'status' => 1,
            ],
        );
        $service = ClinicsService::updateOrCreate(
            ['name_en' => 'QA Clinic Consultation'],
            [
                'name' => 'QA Clinic Consultation',
                'name_ar' => 'استشارة العيادة التجريبية',
                'name_en' => 'QA Clinic Consultation',
                'description' => 'Local Clinics QA service.',
                'description_ar' => 'خدمة اختبار محلية للعيادات.',
                'description_en' => 'Local Clinics QA service.',
                'charges' => 300,
                'vendor_id' => $clinicOwner->id,
                'status' => 1,
            ],
        );
        $otherService = ClinicsService::updateOrCreate(
            ['name_en' => 'QA Clinic Specialist'],
            [
                'name' => 'QA Clinic Specialist',
                'name_ar' => 'أخصائي العيادة التجريبي',
                'name_en' => 'QA Clinic Specialist',
                'description' => 'Second local Clinics QA service.',
                'description_ar' => 'خدمة اختبار محلية ثانية للعيادات.',
                'description_en' => 'Second local Clinics QA service.',
                'charges' => 900,
                'vendor_id' => $clinicOwner->id,
                'status' => 1,
            ],
        );
        ClinicServiceMapping::firstOrCreate(['clinic_id' => $clinic->id, 'service_id' => $service->id]);
        ClinicServiceMapping::firstOrCreate(['clinic_id' => $otherClinic->id, 'service_id' => $otherService->id]);
        ClinicGallery::firstOrCreate([
            'clinic_id' => $clinic->id,
            'full_url' => '/dummy-images/clinic/center/harmony_medical_center.png',
        ], ['status' => 1]);

        $doctorUser = User::updateOrCreate(
            ['email' => 'qa.clinic.doctor@espitalia.local'],
            [
                'first_name' => 'QA',
                'last_name' => 'Doctor',
                'password' => Hash::make('QaDoctor2026'),
                'mobile' => '01000000013',
                'user_type' => 'doctor',
                'gender' => 'male',
                'email_verified_at' => now(),
            ],
        );
        $doctor = Doctor::updateOrCreate(
            ['doctor_id' => $doctorUser->id],
            [
                'vendor_id' => $clinicOwner->id,
                'description' => 'Local Clinics QA doctor.',
                'description_ar' => 'طبيب اختبار محلي للعيادات.',
                'description_en' => 'Local Clinics QA doctor.',
                'governorate_id' => $cairo->id,
                'city_id' => $nasrCity->id,
                'status' => 1,
            ],
        );
        DoctorClinicMapping::firstOrCreate(['doctor_id' => $doctorUser->id, 'clinic_id' => $clinic->id]);
        DoctorServiceMapping::firstOrCreate(
            ['doctor_id' => $doctorUser->id, 'clinic_id' => $clinic->id, 'service_id' => $service->id],
            ['charges' => 300, 'status' => 1],
        );

        $this->command?->info("QA patient: {$patient->email} / QaPatient2026 (id {$patient->id})");
    }
}
