<?php

namespace Modules\Appointment\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Constant\Models\Constant;
use Modules\Appointment\Transformers\ConstantResource;
use Modules\Appointment\Models\EncounterMedicalReport;
use Modules\Appointment\Transformers\MedicalReportRescource;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Appointment\Transformers\PrescriptionRescource;
use Modules\Appointment\Models\EncouterMedicalHistroy;
use Modules\Appointment\Models\EncounterOtherDetails;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Appointment\Transformers\EncounterDashboardDetailsResource;
use Modules\Appointment\Models\BillingRecord;
use Modules\Appointment\Transformers\BillingRecordResource;
use Modules\Appointment\Transformers\BillingRecordDetailsResource;
use Modules\Appointment\Models\AppointmentPatientBodychart;
use Modules\Appointment\Transformers\BodyChartResource;
use Modules\Appointment\Transformers\EncounterServiceResource;

class EncounterDashboardController extends Controller
{
    public function encounterDropdownList(Request $request){
        $perPage = $request->input('per_page', 10); 

        $type = $request->type;
        $data = Constant::where('type', $type);

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $data->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%");
                  
            });
        }  

        if($perPage=='all'){
            $data = $data->get();
        }
        else{
            $data = $data->paginate($perPage);
        }

        $constantCollection = ConstantResource::collection($data);

        return response()->json([
            'status' => true,
            'data' => $constantCollection,
            'message' => __('appointment.encounter_dropdown_list'),
        ], 200);
    }


    public function  GetMedicalReport(Request $request){

        $medical_report=[];

        $perPage = $request->input('per_page', 10); 

         $encounter = $this->accessibleEncounter($request->encounter_id);
         $data= EncounterMedicalReport::where('encounter_id',$encounter->id);

        if($perPage=='all'){
            $data = $data->get();
        }
        else{
            $data = $data->paginate($perPage);
        }

        $reportCollection = MedicalReportRescource::collection($data);

        return response()->json([
            'status' => true,
            'data' =>  $reportCollection,
            'message' => __('appointment.medical_report'),
        ], 200);
        
    }

    public function  GetPrescription(Request $request){

        $prescription=[];

        $perPage = $request->input('per_page', 10); 

         $encounter = $this->accessibleEncounter($request->encounter_id);
         $data= EncounterPrescription::where('encounter_id',$encounter->id);

        if($perPage=='all'){
            $data = $data->get();
        }
        else{
            $data = $data->paginate($perPage);
        }

        $prescriptionCollection = PrescriptionRescource::collection($data);

        return response()->json([
            'status' => true,
            'data' =>  $prescriptionCollection,
            'message' => __('appointment.prescription_list'),
        ], 200);
        
    }
        
    public function saveEncounterDashboard(Request $request){
        $encounter = $this->accessibleEncounter($request->encounter_id);
        $user_id = $encounter->user_id;
        $encounter_id = $encounter->id;
        $problems = $request->input('problems', []);
        $observations = $request->input('observations', []);
        $notes = $request->input('notes', []);
        $prescriptions = $request->input('prescriptions', []);

        EncouterMedicalHistroy::where('encounter_id', $encounter_id)->delete();
        foreach($problems as $problem){
            $encounter_problem = new EncouterMedicalHistroy;
            $encounter_problem->encounter_id = $encounter_id;
            $encounter_problem->user_id = $user_id;
            $encounter_problem->type = 'encounter_problem';
            $encounter_problem->title = $problem['problem_name'];
            $encounter_problem->save();
            
            if(empty($problem['problem_id'])){
                $constant = new Constant;
                $constant->name = $problem['problem_name'];
                $constant->type = 'encounter_problem';
                $constant->value = $problem['problem_name'];
                $constant->save();
            }
        }
        foreach($observations as $observation){
            $encounter_observation = new EncouterMedicalHistroy;
            $encounter_observation->encounter_id = $encounter_id;
            $encounter_observation->user_id = $user_id;
            $encounter_observation->type = 'encounter_observations';
            $encounter_observation->title = $observation['observation_name'];
            $encounter_observation->save();
            
            if(empty($observation['observation_id'])){
                $constant = new Constant;
                $constant->name = $observation['observation_name'];
                $constant->type = 'encounter_observations';
                $constant->value = $observation['observation_name'];
                $constant->save();
            }
        }
        foreach($notes as $note){
            $encounter_notes = new EncouterMedicalHistroy;
            $encounter_notes->encounter_id = $encounter_id;
            $encounter_notes->user_id = $user_id;
            $encounter_notes->type = 'encounter_notes';
            $encounter_notes->title = $note;
            $encounter_notes->save();
        }

        EncounterPrescription::where('encounter_id', $encounter_id)->delete();

        foreach($prescriptions as $prescription){
            $encounter_prescription = new EncounterPrescription;
            $encounter_prescription->encounter_id = $encounter_id;
            $encounter_prescription->user_id = $user_id;
            $encounter_prescription->name = $prescription['name'];
            $encounter_prescription->frequency = $prescription['frequency'];
            $encounter_prescription->duration = $prescription['duration'];
            $encounter_prescription->instruction = $prescription['instruction'];
            $encounter_prescription->save();
        }

        $other_info = new EncounterOtherDetails;
        $other_info->encounter_id = $encounter_id;
        $other_info->user_id = $user_id;
        $other_info->other_details = $request->other_information;
        $other_info->save();


        return response()->json([
            'status' => true,
            'message' => __('appointment.encounter_dashboard_save'),
        ], 200);
    }

    public function encounterDashboardDetail(Request $request){
        $encounter_id = $request->encounter_id;
        $encounter_data = $this->accessibleEncounter($encounter_id)
            ->load('user','clinic','doctor','medicalHistroy','prescriptions','EncounterOtherDetails','medicalReport','bodyChart','soap');

        $responseData = New EncounterDashboardDetailsResource($encounter_data);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('appointment.encounter_dashboard_detail'),
        ], 200);
    }

    public function billingList(Request $request){
        $perPage = $request->input('per_page', 10);

        $billing_records = BillingRecord::with('user','clinic','doctor','clinicservice','patientencounter');

        if ($this->isPatientRequest()) {
            $billing_records->whereHas('patientencounter', function ($query) {
                $query->where('user_id', auth()->id());
            });
        }

        $billing_records = $billing_records->orderBy('updated_at', 'desc');

        $billing_records = $billing_records->paginate($perPage);

        $responseData = BillingRecordResource::collection($billing_records);

        if($request->filled('encounter_id')){
    
            $encounter = $this->accessibleEncounter($request->encounter_id);
            $billing_data = BillingRecord::where('encounter_id',$encounter->id)
                ->with(['user','clinic','doctor','clinicservice.doctor_service','billingItem'])
                ->first();
            // dd($billing_data);
            if (!$billing_data) {
                return response()->json([
                    'status' => false,
                    'message' => __('appointment.billing_record_not_found'),
                ], 404);
            }

            $responseData = New BillingRecordDetailsResource($billing_data);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('appointment.billing_record_details'),
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('appointment.billing_record_list'),
        ], 200);
    }

    public function saveBodychart(Request $request){

        $encounter = $this->accessibleEncounter($request->encounter_id);
        $data = $request->only('name', 'description', 'appointment_id');
        $data['encounter_id'] = $encounter->id;
        $data['patient_id'] = $encounter->user_id;
        $bodychart = AppointmentPatientBodychart::create($data);
        if ($request->hasFile('file_url')) {
            $request->validate(['file_url' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
            storeMediaFile($bodychart, $request->file('file_url'));
        }

        return response()->json([
            'status' => true,
            'message' => __('appointment.bodychart_save'),
        ], 200);
    }

    public function updateBodychart(Request $request, $id)
    {
        $image_handling = setting('image_handling');
        $record = AppointmentPatientBodychart::whereKey($id)
            ->whereHas('patient_encounter', function ($query) {
                if ($this->isPatientRequest()) {
                    $query->where('user_id', auth()->id());
                }
            })
            ->firstOrFail();
        $data = $request->only('name', 'description', 'appointment_id');
        $data['encounter_id'] = $record->encounter_id;
        $data['patient_id'] = $record->patient_id;
    
        if ($record && $image_handling === 'Saved_image') {
            $record->update($data);
            if ($request->hasFile('file_url')) {
                $request->validate(['file_url' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
                storeMediaFile($record, $request->file('file_url'));
            }
        } else {
            $record = AppointmentPatientBodychart::create($data);
            if ($request->hasFile('file_url')) {
                $request->validate(['file_url' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
                storeMediaFile($record, $request->file('file_url'));
            }
        }
        return response()->json([
            'status' => true,
            'message' => __('appointment.bodychart_update'),
        ], 200);
    }

    public function deleteBodychart(Request $request, $id)
    {
        $data = AppointmentPatientBodychart::whereKey($id)
            ->whereHas('patient_encounter', function ($query) {
                if ($this->isPatientRequest()) {
                    $query->where('user_id', auth()->id());
                }
            })
            ->firstOrFail();
        $data->delete();
        
        return response()->json([
            'status' => true,
            'message' => __('appointment.bodychart_delete'),
        ], 200);
    }

    public function bodyChartList(Request $request){
        $perPage = $request->input('per_page', 10);
        $encounter = $this->accessibleEncounter($request->encounter_id);
        $encounter_id = $encounter->id;

        $bodychart_data = AppointmentPatientBodychart::with('patient_encounter')->where('encounter_id', $encounter_id);

        $bodychart_data = $bodychart_data->orderBy('updated_at', 'desc');

        $bodychart_data = $bodychart_data->paginate($perPage);

        $responseData = BodyChartResource::collection($bodychart_data);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('appointment.bodychart_list'),
        ], 200);
    }

    public function encounterServiceDetails(Request $request){
        $encounter_id = $request->encounter_id;
        $encounter_data = $this->accessibleEncounter($encounter_id)
            ->load('user','clinic','doctor','appointment','medicalHistroy','prescriptions','EncounterOtherDetails','medicalReport');

        $responseData = New EncounterServiceResource($encounter_data);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('appointment.encounter_service_detail'),
        ], 200);
    }

    private function isPatientRequest(): bool
    {
        $user = auth()->user();

        return $user !== null && (
            $user->user_type === 'user'
            || (method_exists($user, 'hasRole') && $user->hasRole('user'))
        );
    }

    private function accessibleEncounter($encounterId): PatientEncounter
    {
        $query = PatientEncounter::query();

        if ($this->isPatientRequest()) {
            $query->where('user_id', auth()->id());
        }

        return $query->whereKey($encounterId)->firstOrFail();
    }
}
