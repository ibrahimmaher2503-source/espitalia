<?php

namespace Modules\Clinic\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Clinic\Transformers\DoctorServiceMappingResource;
use Modules\Clinic\Transformers\DoctorClinicMappingRescource;
use Modules\Clinic\Transformers\EmployeeCommissionResource;


class DoctorDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $total_services = optional($this->doctorService)->count() ?? 0;
        $total_reviews = optional($this->doctorReviews)->count() ?? 0;
        $average_rating = optional($this->doctorReviews)->avg('rating') ?? 0;
        $doctor_services = $this->doctorService ?? collect();
        $doctorUserId = optional($this->user)->id;
        $services = null;

        foreach($doctor_services as $doctor_service){
            $clinic_names = [];
            $clinics = optional($doctor_service->clinicservice)->ClinicServiceMapping ?? collect();
            foreach($clinics as $clinic){
                $clinic_names[] = optional($clinic->center)->name;
            }

            $appointments_count = 0;
            if ($doctor_service->clinicservice && $doctorUserId) {
                $appointments_count = $doctor_service->clinicservice->appointmentService->where('doctor_id', $doctorUserId)->count();
            }

            if(auth()->user()){
                $services = DoctorServiceMappingResource::collection($this->doctorService);
            }
            else{
                $services[] = [
                    'service_name' => optional($doctor_service->clinicservice)->name,
                    'total_appointments' => $appointments_count,
                    'clinic_name' => $clinic_names,
                ];
            }
        }
        
        
        return [
            'id' => $this->id,
            'doctor_id' =>optional($this->user)->id,
            'first_name' =>optional($this->user)->first_name,
            'last_name' =>optional($this->user)->last_name,
            'full_name' =>optional($this->user)->full_name,
            'email' => optional($this->user)->email,
            'mobile' =>optional($this->user)->mobile,
            'gender' =>optional($this->user)->gender,
            'expert' => optional(optional($this->user)->profile)->expert,
            'expert_ar' => optional(optional($this->user)->profile)->expert_ar,
            'expert_en' => optional(optional($this->user)->profile)->expert_en,
            'date_of_birth' =>optional($this->user)->date_of_birth,
            'status' =>optional($this->user)->status,
            'is_independent' => (bool) $this->is_independent,
            'about_self' => optional(optional($this->user)->profile)->about_self,
            'about_self_ar' => optional(optional($this->user)->profile)->about_self_ar,
            'about_self_en' => optional(optional($this->user)->profile)->about_self_en,
            'facebook_link' => optional(optional($this->user)->profile)->facebook_link,
            'instagram_link' => optional(optional($this->user)->profile)->instagram_link,
            'twitter_link' => optional(optional($this->user)->profile)->twitter_link,
            'dribbble_link' => optional(optional($this->user)->profile)->dribbble_link,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'signature' => $this->Signature,
            'experience' => $this->experience,
            'experience_ar' => $this->experience_ar,
            'experience_en' => $this->experience_en,
            'title' => $this->title ?? '',
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'country_id' =>optional($this->user)->country,
            'state_id' => optional($this->user)->state,
            'city_id' =>optional($this->user)->city,
            'country_name' => optional(optional($this->user)->countries)->name,
            'state_name' => optional(optional($this->user)->states)->name,
            'city_name' => optional(optional($this->user)->cities)->name,
            'address' => optional($this->user)->address,
            'pincode' => optional($this->user)->pincode,
            'latitude' => optional($this->user)->latitude,
            'longitude' => optional($this->user)->longitude,
            // 'services'=>DoctorServiceMappingResource::collection($this->doctorService),
            'clinics'=>DoctorClinicMappingRescource::collection($this->doctorclinic),
            'commissions'=>EmployeeCommissionResource::collection($this->doctorCommission),
            'profile_image' =>optional($this->user)->profile_image,
            'total_services' => $total_services,
            'total_reviews' => $total_reviews,
            'average_rating' => round($average_rating, 2),
            'reviews' => $this->doctorReviews,
            'qualifications' => $this->doctorDocuments,
            'services' => $services,
            'specializations' => $this->whenLoaded('specializations', fn () => $this->specializations->map(fn ($s) => [
                'id'   => $s->id,
                'name' => $s->name,
                'name_ar' => $s->name_ar,
                'name_en' => $s->name_en,
                'slug' => $s->slug,
            ])),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
