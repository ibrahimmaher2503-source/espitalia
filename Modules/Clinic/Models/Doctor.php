<?php

namespace Modules\Clinic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Clinic\Database\factories\DoctorFactory;
use App\Models\Traits\HasSlug;
use App\Trait\CustomFieldsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use Modules\Clinic\Models\ClinicsService;
use App\Models\User;
use Modules\Clinic\Models\Clinics;
use Auth;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Commission\Models\EmployeeCommission;
use Modules\Clinic\Models\Receptionist;

class Doctor extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use HasSlug;
    use CustomFieldsTrait;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'doctors';
    protected $fillable = ['doctor_id', 'experience', 'signature', 'vendor_id', 'description', 'governorate_id', 'city_id', 'experience_ar', 'experience_en', 'description_ar', 'description_en', 'title_ar', 'title_en', 'license_number', 'is_active', 'is_independent'];
    protected $casts = [

        'doctor_id' => 'integer',
        'status' => 'boolean',
        'vendor_id' => 'integer',
    ];
    const CUSTOM_FIELD_MODEL = 'Modules\Clinic\Models\Doctor';

    public function getExperienceAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->experience_ar : $this->experience_en;

        return $localized ?: ($locale === 'ar' ? $this->experience_en : $this->experience_ar) ?: $this->attributes['experience'] ?? '';
    }

    public function getDescriptionAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->description_ar : $this->description_en;

        return $localized ?: ($locale === 'ar' ? $this->description_en : $this->description_ar) ?: $this->attributes['description'] ?? '';
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->title_ar : $this->title_en;

        return $localized ?: ($locale === 'ar' ? $this->title_en : $this->title_ar) ?: '';
    }
    protected static function newFactory(): DoctorFactory
    {
        return DoctorFactory::new();
    }
    public function clinicservices()
    {
        return $this->belongsTo(ClinicsService::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }


    public function scopeSetVendor($query)
    {
        $vendorId = Auth::id();
        return $query->where('vendor_id', $vendorId);
    }
    public function doctorService()
    {
        return $this->hasMany(DoctorServiceMapping::class,'doctor_id','doctor_id')->with('clinicservice');
    }
    public function doctorclinic()
    {
        return $this->hasMany(DoctorClinicMapping::class, 'doctor_id','doctor_id');
    }

    public function doctorSessions()
    {
        return $this->hasMany(DoctorSession::class,'doctor_id','doctor_id');
    }

    public function doctorReviews()
    {
        return $this->hasMany(DoctorRating::class, 'doctor_id','doctor_id');
    }
    public function doctorDocuments()
    {
        return $this->hasMany(DoctorDocument::class, 'doctor_id','doctor_id');
    }

    public function doctorCommission()
    {
        return $this->hasMany(EmployeeCommission::class, 'employee_id','doctor_id');
    }

    public function doctorCallServices()
    {
        return $this->hasMany(DoctorCallService::class, 'doctor_id', 'doctor_id');
    }

    public function governorate()
    {
        return $this->belongsTo(\Modules\Location\Models\Governorate::class);
    }

    public function city()
    {
        return $this->belongsTo(\Modules\Location\Models\GovernorateCity::class, 'city_id');
    }

    public function doctorCallSessions()
    {
        return $this->hasMany(DoctorCallSession::class, 'doctor_id', 'doctor_id');
    }

    public function specializations()
    {
        return $this->belongsToMany(\Modules\Specialization\Models\Specialization::class, 'doctor_specialization')->withTimestamps();
    }

    public function scopeCheckMultivendor($query){
        if(multiVendor() == "0") {
            $userIds = User::whereIn('user_type', ['admin','demo_admin'])->pluck('id');
            $query = $query->where('status',1);

            if ($userIds->isNotEmpty()) {
                $query->whereIn('vendor_id', $userIds);
            }

            return $query;
        }

        return $query->where('status',1);
    }

    public function scopeSetRole($query, $user)
    {
       $user_id = $user->id;
       
       if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
           if (multiVendor() == "0") {

            $user_ids = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'demo_admin']))->pluck('id');
            $query = $query->whereIn('vendor_id', $user_ids);
           }
           return $query; 
       }       
       
       if($user->hasRole('vendor')) {  

           $query=$query->where('vendor_id',  $user_id);

           return $query; 
        }

        if(auth()->user()->hasRole('doctor')) {


           $query=$query->where('doctor_id',$user_id);

           return $query;
        
        }

        if(auth()->user()->hasRole('receptionist')){

            $Receptionist=Receptionist::where('receptionist_id',$user_id)->first();

            if (!$Receptionist) {
                return $query->whereRaw('1 = 0');
            }

            $vendorId=$Receptionist->vendor_id;
            $clinic_id=$Receptionist->clinic_id;

            if(multiVendor() == "0"){

                $query->where('vendor_id', $vendorId)->with('doctorclinic')->whereHas('doctorclinic', function ($query) use ($clinic_id) {
                    $query->where('clinic_id', $clinic_id);
                 });

             }else{

                  $query->with('doctorclinic')->whereHas('doctorclinic', function ($query) use ($clinic_id) {
                      $query->where('clinic_id', $clinic_id);
                   });
              
               }
            // $query = $query->with('doctorService')->whereHas('doctorService', function ($query) use ($clinic_id) {
            //             $query->where('clinic_id', $clinic_id);
            //          });
               return $query;
        }


        return $query;
    }


    
}
