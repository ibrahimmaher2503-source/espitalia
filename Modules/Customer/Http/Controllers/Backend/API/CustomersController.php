<?php

namespace Modules\Customer\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Modules\Customer\Models\OtherPatient;
use Illuminate\Support\Facades\Auth;
use Modules\Customer\Http\Requests\OtherRequest;



class CustomersController extends Controller
{
    public array $data = [];

    /**
     * Display a listing of the resource.
     */
    public function getPatientList(Request $request): JsonResponse
    {
        $user = auth()->user();

        $patients = [
            [
                'name' => 'Myself',
                'image' => $user->profile_image ? asset('storage/' . $user->profile_image) : asset('default-avatar.png'),
            ]
        ];

        $otherPatients = OtherPatient::where('user_id', $user->id)->get();

        foreach ($otherPatients as $patient) {
            $patients[] = [
                'name' => $patient->first_name . ' ' . $patient->last_name,
                'image' => $patient->profile_image ? asset('storage/' . $patient->profile_image) : asset('default-avatar.png'),
            ];
        }

        return response()->json(['patients' => $patients], 200);
    }

    public function customerDetails(): JsonResponse
    {

        if (auth()->check()) {
            $user = auth()->user();

            $otherPatients = OtherPatient::where('user_id', $user->id)->get();

            return response()->json([
                'user' => $user,
                'patientdetails' => $otherPatients
            ]);
        }

        return response()->json(['error' => 'User not authenticated'], 401);
    }

    public function getOtherMembersList(Request $request)
    {
        try {
            $otherMembers = OtherPatient::where('user_id', auth()->id())
                ->latest()
                ->get();

            // Return success response
            return response()->json([
                'status'  => true,
                'code'    => 200,
                'message' => __('messages.records_fetched_successfully'),
                'data'    => $otherMembers,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => __('messages.something_went_wrong'),
            ], 500);
        }
    }

    public function addOtherMember(OtherRequest $request)
    {
        $data = $request->validated();
        $userId = auth()->id();
        $memberId = $data['id'] ?? null;
        unset($data['id'], $data['profile_image']);
        $data['user_id'] = $userId;
        // Determine if it's an update or a new record
        if (!empty($memberId)) {
            $otherMemberList = OtherPatient::whereKey($memberId)
                ->where('user_id', $userId)
                ->first();
            if (!$otherMemberList) {
                return response()->json([
                    'status' => false,
                    'code' => 404,
                    'message' => __('messages.record_not_found'),
                ], 404);
            }
            unset($data['user_id']);
            $otherMemberList->update($data);
            $message = __('messages.member_updated');
        } else {
            $otherMemberList = OtherPatient::create($data);
            $message = __('messages.member_added');
        }
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            storeMediaFile($otherMemberList, $request->file('profile_image'), 'profile_image');
        }
        // Return success response
        return response()->json([
            'status' => true,
            'code' => 200,
            'data' => $otherMemberList,
            'message' => $message,
        ], 200);
    }

    public function deleteOtherMember($id)
    {
        
        // Retrieve the record by ID
        $otherMember = OtherPatient::whereKey($id)
            ->where('user_id', auth()->id())
            ->first();

        // Check if the record exists
        if (!$otherMember) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => __('messages.record_not_found'),
            ], 404);
        }

        // Delete the record
        $otherMember->delete();

        // Return success response
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => __('messages.record_deleted_successfully'),
        ], 200);
       
    }

}
