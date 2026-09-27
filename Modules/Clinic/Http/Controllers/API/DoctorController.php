<?php

namespace Modules\Clinic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Clinic\Models\Doctor;
use App\Models\User;
use Modules\Clinic\Transformers\DoctorResource;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Clinic\Transformers\DoctorSessionListResource;
use Auth;
use Modules\Clinic\Models\DoctorSession;
use Modules\Clinic\Models\DoctorRating;
use Modules\Clinic\Transformers\DoctorReviewResource;
use Modules\Clinic\Transformers\DoctorDetailsResource;
use Modules\Commission\Models\Commission;
use Modules\Clinic\Transformers\CommissionResource;
use Modules\Clinic\Transformers\PatientResource;
use Modules\Clinic\Transformers\PatientDetailsResource;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Clinic\Transformers\PayoutHistoryResource;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Transformers\ReceptionistResource;
use Modules\Clinic\Models\DoctorServiceMapping;
use Modules\Clinic\Trait\ClinicTrait;
use Modules\Clinic\Models\ClinicsService;
use Modules\FrontendSetting\Models\FrontendSetting;

class DoctorController extends Controller
{
    use ClinicTrait;
    public function DoctorList(Request $request){

        $rawPerPage = $request->input('per_page', 10);
        // Allow 'all' only for admin/demo_admin; cap regular users at 50
        if ($rawPerPage === 'all') {
            $perPage = (auth()->user()?->hasAnyRole(['admin', 'demo_admin'])) ? 'all' : 50;
        } else {
            $perPage = min((int) $rawPerPage, 50);
        }

        if(auth()->user()){
            $doctor_list = Doctor::CheckMultivendor()->SetRole(auth()->user())->with('user.profile','doctorCommission','doctorService','doctorclinic','specializations');
        }
        else{
            $doctor_list = Doctor::CheckMultivendor()->with('user.profile','doctorCommission','doctorService','doctorclinic','specializations')->where('status',1);
        }

        $doctor_list->whereHas('user');

        if ($request->has('search')) {
            $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $request->input('search'));
            $doctor_list->whereHas('user', function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name_ar', 'like', "%{$searchTerm}%")
                        ->orWhere('first_name_en', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name_ar', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name_en', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            });
        }
        if($request->filled('is_popular') && $request->is_popular == 1) {
            $sectionData = [];
            $section = FrontendSetting::where('key', 'section_6')->first();
            $sectionData = $section ? json_decode($section->value, true) : null;
            
            $doctorIds = $sectionData['doctor_id'] ?? [];
            if (is_array($doctorIds) && !empty($doctorIds)) {
                $doctor_list->whereIn('id', $doctorIds);
            }
        }

        if($request->filled('clinic_id')){

            $clinicId=$request->clinic_id;
            $doctor_list->whereHas('doctorclinic', function ($query) use ($clinicId) {
                $query->where('clinic_id',$clinicId);
            });
        }

        if($request->filled('service_id')){

            $serviceId=$request->service_id;
            $doctor_list->whereHas('doctorService', function ($query) use ($serviceId) {
                $query->where('service_id',$serviceId);
            });
        }

        if($request->filled('vendor_id')){
            $doctor_list->where('vendor_id',$request->vendor_id);
         }

        if ($request->filled('governorate_id')) {
            $doctor_list->where('governorate_id', $request->governorate_id);
        }

        if ($request->filled('city_id')) {
            $doctor_list->where('city_id', $request->city_id);
        }
        
        if ($request->filled('specialization_ids') && is_array($request->specialization_ids)) {
            $specIds = $request->specialization_ids;
            $doctor_list->whereHas('specializations', function ($query) use ($specIds) {
                $query->whereIn('specializations.id', $specIds);
            });
        }

        if ($request->filled('is_rating_min') || $request->filled('is_rating_max')) {
            $minRating = $request->input('is_rating_min', 0);
            $maxRating = $request->input('is_rating_max', 5);

            $doctor_list->whereHas('doctorReviews', function ($query) use ($minRating, $maxRating) {
                $query->select('doctor_id')
                    ->groupBy('doctor_id')
                    ->havingRaw('AVG(rating) BETWEEN ? AND ?', [$minRating, $maxRating]);
            });
        }
        if($request->filled('doctor_id')){
            $doctor = $doctor_list->where('doctor_id',$request->doctor_id)->first();

            if (!$doctor) {
                return response()->json([
                    'status' => false,
                    'data' => null,
                    'message' => __('messages.record_not_found'),
                ], 404);
            }

            $responseData = new DoctorDetailsResource($doctor);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.doctor_details'),
            ], 200);
        }

        $doctor = $doctor_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){
            $doctor = $doctor->get();
        }else{
            $doctor = $doctor->paginate($perPage);
        }

        $responseData = DoctorResource::collection($doctor);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.doctor_list'),
        ], 200);
    }


    public function doctorDetails($id)
    {
        $doctor = Doctor::with('user', 'doctorCommission', 'doctorService', 'doctorclinic', 'doctorReviews', 'doctorDocuments', 'specializations')
            ->whereHas('user')
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('doctor_id', $id);
            })
            ->first();

        if (! $doctor || (! auth()->user() && (int) $doctor->status !== 1)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.not_found'),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new DoctorDetailsResource($doctor),
            'message' => __('clinic.doctor_details'),
        ], 200);
    }

     public function DoctorSessionList(Request $request){

        $perPage = $request->input('per_page', 10);

        if(auth()->check() && auth()->user()->hasRole('doctor')){
            $doctor_session_list=DoctorClinicMapping::CheckMultivendor()->with('doctorsession')->where('doctor_id', auth()->user()->id);
        }
        else{
            $doctor_session_list=DoctorClinicMapping::CheckMultivendor()->with('doctorsession');
        }

        if($request->filled('clinic_id')){
            $clinicId=$request->clinic_id;
            $doctor_session_list = $doctor_session_list->where('clinic_id',$clinicId);
        }

        $doctor_session_list = $doctor_session_list->orderBy('updated_at', 'desc');

        $doctor_session_list  = $doctor_session_list->paginate($perPage);

        $responseData = DoctorSessionListResource::collection($doctor_session_list);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.doctor_list'),
        ], 200);



     }

    public function saveRating(Request $request)
    {
        $request->validate([
            'doctor_id'  => 'required|integer|exists:users,id',
            'rating'     => 'required|numeric|min:1|max:5',
            'title'      => 'nullable|string|max:255',
            'review_msg' => 'nullable|string|max:2000',
            'service_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $isNew = false;

        if ($request->filled('id')) {
            // Update: enforce ownership — only the rating's author can update it
            $rating = DoctorRating::where('id', $request->id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $rating->update($request->only(['title', 'review_msg', 'rating', 'doctor_id', 'service_id']));
        } else {
            $rating = DoctorRating::create(
                $request->only(['title', 'review_msg', 'rating', 'doctor_id', 'service_id']) + ['user_id' => $user->id]
            );
            $isNew = true;
        }

        $message = $isNew ? __('clinic.rating_add') : __('clinic.rating_update');

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function deleteRating(Request $request)
    {
        $user = auth()->user();
        $rating = DoctorRating::where('id', $request->id)->where('user_id', $user->id)->first();
        if ($rating == null) {
            return response()->json(['status' => false, 'message' => __('clinic.rating_notfound')], 404);
        }
        $message = __('clinic.rating_delete');
        $rating->delete();

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function getRating(Request $request)
    {
        $doctor_id = $request->doctor_id;

        $perPage = min((int) $request->input('per_page', 10), 50);

        $reviews = DoctorRating::with('clinic_service');

        if($request->filled('doctor_id')){
            $reviews = $reviews->where('doctor_id', $doctor_id);
        } elseif(!empty($request->clinic_id)) {
            $clinic_doctors = DoctorClinicMapping::where('clinic_id', $request->clinic_id)->pluck('doctor_id');
            $reviews = $reviews->whereIn('doctor_id', $clinic_doctors);
        }

        if($request->filled('service_id')){
            $reviews = $reviews->where('service_id', $request->service_id);
        }

        $reviews = $reviews->orderBy('updated_at', 'desc')->paginate($perPage);
        $review = DoctorReviewResource::collection($reviews);

        return response()->json([
            'status' => true,
            'data' => $review,
            'message' => __('clinic.review_list'),
        ], 200);
    }

    public function doctorCommissionList(Request $request){
        $perPage = min((int) $request->input('per_page', 10), 50);
        $commission_list = Commission::where('type', 'doctor_commission')->where('status', 1);

        $commission = $commission_list->orderBy('updated_at', 'desc');

        $commission = $commission->paginate($perPage);
        $responseData = CommissionResource::collection($commission);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.commission_list'),
        ], 200);
    }

    public function doctorPayoutHistory(Request $request){
        $perPage = $request->input('per_page', 10);
        $payout_data = EmployeeEarning::doctorRole(auth()->user())->with('employee')->where('user_type', 'doctor')->orderBy('updated_at','desc');
        $payout_data = $payout_data->paginate($perPage);
        $responseData = PayoutHistoryResource::collection($payout_data);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.commission_list'),
        ], 200);
    }


    public function patientList(Request $request){

        $perPage = $request->input('per_page', 10);

        if($request->has('filter') && $request->filter == 'all'){

            $patient_list = User::role('user')->with('profile', 'cities', 'states', 'countries')->where('status',1);
        }else{

            $patient_list = User::role('user')->setRolePatients(auth()->user())->with('profile', 'cities', 'states', 'countries');

        }

        if ($request->has('clinic_id')) {
            $clinicId = $request->input('clinic_id');
            $patient_list->whereHas('appointment', function ($query) use ($clinicId) {
                $query->whereHas('cliniccenter', function ($subQuery) use ($clinicId) {
                    $subQuery->where('clinic_id', $clinicId);
                });
            });
        }
        if ($request->has('search')) {
            $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $request->input('search'));
            $patient_list->where(function ($query) use ($searchTerm) {
                $query->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // $patient_list = $patient_list->where('user_type', 'user')->where('status', 1);

        if($request->filled('patient_id')){
            $patient = $patient_list->where('id',$request->patient_id)->first();

            if (!$patient) {
                return response()->json([
                    'status' => false,
                    'data' => null,
                    'message' => __('messages.record_not_found'),
                ], 404);
            }

            $responseData = new PatientDetailsResource($patient);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.patient_details'),
            ], 200);
        }

        $patient = $patient_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){
            $patient = $patient->get();
        }else{
            $patient = $patient->paginate($perPage);
        }

        $responseData = PatientResource::collection($patient);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.patient_list'),
        ], 200);
    }


    public function receptionistList(Request $request){

        $perPage = $request->input('per_page', 10);
        $receptionist_list = Receptionist::with('users', 'clinics');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $receptionist_list->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%");
        }

        $receptionist = $receptionist_list->orderBy('updated_at', 'desc');

        if($perPage=='all'){

            $receptionist = $receptionist->get();

        }else{

            $receptionist = $receptionist->paginate($perPage);
        }

        $responseData = ReceptionistResource::collection($receptionist);

        if($request->filled('receptionist_id')){

            $receptionist = $receptionist_list->where('id',$request->receptionist_id)->first();

            if (!$receptionist) {
                return response()->json([
                    'status' => false,
                    'data' => null,
                    'message' => __('messages.record_not_found'),
                ], 404);
            }

            $responseData = New ReceptionistResource($receptionist);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.receptionist_details'),
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.receptionist_list'),
        ], 200);
    }


    public function doctorSession(Request $request){

        $doctor_session=[];

        if($request->has('clinic_id') && $request->clinic_id && $request->has('doctor_id') && $request->doctor_id){

            $doctor_session=DoctorSession::where('doctor_id', $request->doctor_id)->where('clinic_id',$request->clinic_id)->get();

        }

        return response()->json([
            'status' => true,
            'data' => $doctor_session,
            'message' => __('clinic.doctor_session'),
        ], 200);

    }


    public function assignDoctor(Request $request){
        $user = auth()->user();
        $service_id = $request->service_id;
        $clinic_id = $request->clinic_id;
        $assign_doctors = $request->assign_doctors;
        $service = ClinicsService::findOrFail($service_id);
        if(!empty($assign_doctors)) {

            foreach($assign_doctors as $doctor) {
                $service['charges'] = $doctor['price'] ?? 0;
                if($service['discount']==0){
    
                    $service['discount_value']=0;
                    $service['discount_type']=null;
                    $service['service_discount_price'] = $service['charges'];
                }else{
                    $service['discount_price'] = $service['discount_type'] == 'percentage' ? $service['charges'] * $service['discount_value'] / 100 : $service['discount_value'];
                    $service['service_discount_price'] = $service['charges'] - $service['discount_price'];
                 }
                $inclusive_tax_price = $this->inclusiveTaxPrice($service);
                $doctor['inclusive_tax_price'] = $inclusive_tax_price['inclusive_tax_price'];
                $data = [
                    'service_id' => $service_id,
                    'clinic_id' => $clinic_id,
                    'doctor_id' => $doctor['doctor_id'],
                    'charges' => $doctor['price'],
                    'inclusive_tax_price' => $doctor['inclusive_tax_price'] ?? 0,
                ];

                DoctorServiceMapping::updateOrCreate(
                    ['service_id' => $service_id, 'clinic_id' => $clinic_id, 'doctor_id' => $doctor['doctor_id']],
                    $data
                );
            }
        }
        if($user->hasRole('doctor')){
            $message = __('clinic.price_update');
        }else{
            $message = __('clinic.assign_doctor');
        }

        return response()->json([
            'status' => true,

            'message' => $message,
        ], 200);
    }




}
