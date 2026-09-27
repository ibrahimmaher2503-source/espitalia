<?php

namespace Modules\Clinic\Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorIndependentService;
use Modules\Clinic\Transformers\DoctorDetailsResource;
use Modules\Clinic\Transformers\DoctorResource;
use Modules\Clinic\Transformers\DoctorIndependentServiceResource;
use Modules\Specialization\Models\Specialization;
use Illuminate\Http\Request;
use Tests\TestCase;

class DoctorBilingualTest extends TestCase
{
    use RefreshDatabase;

    private function createDoctorUser(array $overrides = []): User
    {
        $defaults = [
            'first_name' => 'John',
            'first_name_en' => 'John',
            'first_name_ar' => 'يوحنا',
            'last_name' => 'Doe',
            'last_name_en' => 'Doe',
            'last_name_ar' => 'دو',
            'email' => 'doctor_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'doctor',
            'email_verified_at' => now(),
        ];

        return User::create(array_merge($defaults, $overrides));
    }

    private function createDoctorRecord(User $user, array $overrides = []): Doctor
    {
        $defaults = [
            'doctor_id' => $user->id,
            'experience' => '10 years',
            'experience_en' => '10 years',
            'experience_ar' => '10 سنوات',
            'description' => 'Specialist',
            'description_en' => 'Specialist',
            'description_ar' => 'متخصص',
            'vendor_id' => $user->id,
        ];

        return Doctor::create(array_merge($defaults, $overrides));
    }

    private function createUserProfile(User $user, array $overrides = []): UserProfile
    {
        $defaults = [
            'expert' => 'Cardiology',
            'expert_en' => 'Cardiology',
            'expert_ar' => 'أمراض القلب',
            'about_self' => 'Experienced doctor',
            'about_self_en' => 'Experienced doctor',
            'about_self_ar' => 'طبيب ذو خبرة',
        ];

        return $user->profile()->create(array_merge($defaults, $overrides));
    }

    /** @test */
    public function user_model_returns_english_first_name_in_en_locale()
    {
        $user = $this->createDoctorUser();

        app()->setLocale('en');
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('John Doe', $user->full_name);
    }

    /** @test */
    public function user_model_returns_arabic_first_name_in_ar_locale()
    {
        $user = $this->createDoctorUser();

        app()->setLocale('ar');
        $this->assertEquals('يوحنا', $user->first_name);
        $this->assertEquals('دو', $user->last_name);
    }

    /** @test */
    public function user_model_falls_back_to_en_when_ar_is_empty()
    {
        $user = $this->createDoctorUser([
            'first_name_ar' => null,
            'last_name_ar' => null,
        ]);

        app()->setLocale('ar');
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
    }

    /** @test */
    public function user_model_falls_back_to_ar_when_en_is_empty()
    {
        $user = $this->createDoctorUser([
            'first_name_en' => null,
            'last_name_en' => null,
        ]);

        app()->setLocale('en');
        $this->assertEquals('يوحنا', $user->first_name);
        $this->assertEquals('دو', $user->last_name);
    }

    /** @test */
    public function doctor_model_returns_locale_aware_experience()
    {
        $user = $this->createDoctorUser();
        $doctor = $this->createDoctorRecord($user);

        app()->setLocale('en');
        $this->assertEquals('10 years', $doctor->experience);

        app()->setLocale('ar');
        $this->assertEquals('10 سنوات', $doctor->experience);
    }

    /** @test */
    public function doctor_model_returns_locale_aware_description()
    {
        $user = $this->createDoctorUser();
        $doctor = $this->createDoctorRecord($user);

        app()->setLocale('en');
        $this->assertEquals('Specialist', $doctor->description);

        app()->setLocale('ar');
        $this->assertEquals('متخصص', $doctor->description);
    }

    /** @test */
    public function user_profile_returns_locale_aware_expert()
    {
        $user = $this->createDoctorUser();
        $profile = $this->createUserProfile($user);

        app()->setLocale('en');
        $this->assertEquals('Cardiology', $profile->expert);

        app()->setLocale('ar');
        $this->assertEquals('أمراض القلب', $profile->expert);
    }

    /** @test */
    public function user_profile_returns_locale_aware_about_self()
    {
        $user = $this->createDoctorUser();
        $profile = $this->createUserProfile($user);

        app()->setLocale('en');
        $this->assertEquals('Experienced doctor', $profile->about_self);

        app()->setLocale('ar');
        $this->assertEquals('طبيب ذو خبرة', $profile->about_self);
    }

    /** @test */
    public function bilingual_columns_are_stored_correctly()
    {
        $user = $this->createDoctorUser();
        $doctor = $this->createDoctorRecord($user);
        $profile = $this->createUserProfile($user);

        // Verify raw DB values
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name_en' => 'John',
            'first_name_ar' => 'يوحنا',
            'last_name_en' => 'Doe',
            'last_name_ar' => 'دو',
        ]);

        $this->assertDatabaseHas('doctors', [
            'doctor_id' => $user->id,
            'experience_en' => '10 years',
            'experience_ar' => '10 سنوات',
            'description_en' => 'Specialist',
            'description_ar' => 'متخصص',
        ]);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'expert_en' => 'Cardiology',
            'expert_ar' => 'أمراض القلب',
            'about_self_en' => 'Experienced doctor',
            'about_self_ar' => 'طبيب ذو خبرة',
        ]);
    }

    /** @test */
    public function set_first_name_en_mutator_syncs_original_column()
    {
        $user = $this->createDoctorUser();
        $user->first_name_en = 'Jane';
        $user->save();

        $user->refresh();
        $this->assertEquals('Jane', $user->getRawOriginal('first_name'));
        $this->assertEquals('Jane', $user->first_name_en);
    }

    /** @test */
    public function doctor_resources_keep_raw_bilingual_fields_and_localized_generics()
    {
        $user = $this->createDoctorUser();
        $doctor = $this->createDoctorRecord($user);
        $this->createUserProfile($user);
        $doctor->setRelation('user', $user);
        foreach (['doctorService', 'doctorclinic', 'doctorCommission', 'doctorReviews', 'doctorDocuments'] as $relation) {
            $doctor->setRelation($relation, collect());
        }

        $request = Request::create('/api/doctor-detail');
        app()->setLocale('ar');
        $details = (new DoctorDetailsResource($doctor))->toArray($request);

        $this->assertSame('متخصص', $details['description']);
        $this->assertSame('Specialist', $details['description_en']);
        $this->assertSame('متخصص', $details['description_ar']);
        $this->assertSame('أمراض القلب', $details['expert']);
        $this->assertSame('Cardiology', $details['expert_en']);

        $service = new DoctorIndependentService();
        $service->setRawAttributes([
            'name' => 'Consultation',
            'name_ar' => 'كشف',
            'name_en' => 'Consultation',
            'description' => 'استشارة',
            'description_ar' => 'استشارة',
            'description_en' => 'Consultation',
        ]);
        $serviceData = (new DoctorIndependentServiceResource($service))->toArray($request);

        $this->assertSame('كشف', $serviceData['name_ar']);
        $this->assertSame('Consultation', $serviceData['name_en']);
        $this->assertSame('استشارة', $serviceData['description_ar']);
        $this->assertSame('Consultation', $serviceData['description_en']);
    }

    /** @test */
    public function doctor_list_resource_preserves_bilingual_specialization_names()
    {
        $user = $this->createDoctorUser();
        $doctor = $this->createDoctorRecord($user);
        $specialization = Specialization::create([
            'name_ar' => 'أمراض القلب',
            'name_en' => 'Cardiology',
        ]);

        $doctor->setRelation('user', $user);
        $doctor->setRelation('specializations', collect([$specialization]));
        foreach (['doctorService', 'doctorclinic', 'doctorCommission'] as $relation) {
            $doctor->setRelation($relation, collect());
        }
        $doctor->setAttribute('total_appointment', 0);
        $doctor->setAttribute('average_rating', 0);
        $doctor->setAttribute('total_patient', 0);
        $doctor->setAttribute('total_reviews', 0);

        $data = (new DoctorResource($doctor))->toArray(Request::create('/api/doctors'));

        $this->assertSame('أمراض القلب', $data['specializations'][0]['name_ar']);
        $this->assertSame('Cardiology', $data['specializations'][0]['name_en']);
    }
}
