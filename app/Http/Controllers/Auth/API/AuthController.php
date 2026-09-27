<?php

namespace App\Http\Controllers\Auth\API;

use App\Http\Controllers\Auth\Trait\AuthTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\SocialLoginResource;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Modules\Commission\Models\EmployeeCommission;
use Modules\Commission\Models\Commission;
use App\Models\UserProfile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Modules\Clinic\Models\Doctor;
use App\Services\Auth\FirebaseIdentityTokenVerifier;

class AuthController extends Controller
{
    use AuthTrait;

    public function register(Request $request)
    {
        $user = $this->registerTrait($request);


        // Check if response is an error response
        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }
        $success['token'] = $user->createToken(setting('app_name'))->plainTextToken;
        $success['name'] = $user->name;
        $userResource = new RegisterResource($user);      

        return $this->sendResponse($userResource, __('messages.register_successfull'));
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::withTrashed()->where('email', $request->email)->first();        
        if (!empty($user->deleted_at)) {
            return response()->json([
                'status' => false,
                'is_deleted' => true,
                'message' => __('messages.already_delete'),
            ], 200);
        }
        if ($user == null) {
            return response()->json(['status' => false, 'message' => __('messages.register_before_login')]);
        }
       
        $usertype = $user->user_type;
        $requestedUserType = $request->input('user_type');
        $usertype = $user->user_type;

        if ($requestedUserType != $usertype) {
            return response()->json(['status' => false, 'message' => __('messages.user_type_mismatch')]);
        }
        if ($usertype == "doctor"  || $usertype == "vendor" || $usertype == "receptionist") {

            if (multiVendor() == "0") {
              
                 if ( $usertype == 'vendor' || ( $usertype == 'doctor' && optional($user->doctor)->vendor->user_type == 'vendor') || ( $usertype == 'receptionist' && optional($user->receptionist)->vendor->user_type == 'vendor')) {
                
                        return ['status' => 406, 'message' => __('messages.account_deactivated')];
                  }
                
            }

            if ($user->email_verified_at == null) {

                return response()->json(['status' => false, 'message' => __('messages.account_not_verify')]);
            }
        }
        $request->ensureIsNotRateLimited();
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            RateLimiter::clear($request->throttleKey());
            $user = Auth::user();


            if ($user->is_banned == 1 || $user->status == 0) {
                return response()->json(['status' => false, 'message' => __('messages.login_error')]);
            }

            $user->player_id = $request->input('player_id');
            $user->save();
            Auth::logout();

            return $this->issueApiToken($user);
        } else {
            RateLimiter::hit($request->throttleKey(), 60);
            return $this->sendError(__('messages.not_matched'), ['error' => __('messages.unauthorised')], 200);
        }
    }

    private function issueApiToken(User $user)
    {
        $user['api_token'] = $user->createToken(setting('app_name'))->plainTextToken;
        return $this->sendResponse(new LoginResource($user), __('messages.user_login'));
    }

    public function socialLogin(Request $request, FirebaseIdentityTokenVerifier $identityTokenVerifier)
    {
        $input = $request->validate([
            'login_type' => ['required', 'in:google,apple'],
            'id_token' => ['required', 'string', 'max:8192'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'player_id' => ['nullable', 'string', 'max:512'],
        ]);

        try {
            $claims = $identityTokenVerifier->verify($input['id_token']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_request'),
            ], 401);
        }

        $provider = data_get($claims, 'firebase.sign_in_provider');
        $expectedProvider = $input['login_type'] === 'google' ? 'google.com' : 'apple.com';
        if ($provider !== $expectedProvider) {
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_request'),
            ], 401);
        }

        $input['email'] = strtolower((string) $claims['email']);
        $input['firebase_uid'] = (string) $claims['sub'];
        $input['user_type'] = 'user';
        $emailUser = User::where('email', $input['email'])->first();
        $user_data = User::where('firebase_uid', $input['firebase_uid'])->first() ?: $emailUser;

        if ($user_data?->firebase_uid !== null
            && ! hash_equals((string) $user_data->firebase_uid, $input['firebase_uid'])) {
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_request'),
            ], 401);
        }

        if ($user_data && $user_data->login_type !== $input['login_type']) {
            return response()->json([
                'status' => false,
                'message' => __('messages.already_taken'),
            ], 409);
        }

        if ($user_data != null) {

            if ($user_data->firebase_uid === null) {
                $user_data->forceFill(['firebase_uid' => $input['firebase_uid']])->save();
            }

            $usertype = $user_data->user_type;

            if ($usertype == "doctor"  || $usertype == "vendor") {

                if ($user_data->email_verified_at == null) {

                    return response()->json(['status' => false, 'message' => __('messages.account_not_verify')]);
                }
            }

            if (!isset($user_data->login_type) || $user_data->login_type == '') {

                if ($user_data->login_type == '') {

                    return response()->json(['status' => false, 'message' => __('messages.already_taken')]);
                }

            }

            if (!isset($user_data->login_type) || $user_data->login_type == '') {
                if ($request->login_type === 'google') {
                    $message = __('validation.unique', ['attribute' => 'email']);
                } else {
                    $message = __('validation.unique', ['attribute' => 'username']);
                }

                return $this->sendError($message, 400);
            }
            $message = __('messages.login_success');
        } else {

            if ($request->login_type === 'google' || $request->login_type === 'apple') {
                $key = 'email';
                $value = $request->email;
            } else {
                $key = 'username';
                $value = $request->username;
            }

            $trashed_user_data = User::where($key, $value)->whereNotNull('login_type')->withTrashed()->first();

            if ($trashed_user_data != null && $trashed_user_data->trashed()) {
                if ($request->login_type === 'google') {
                    $message = __('validation.unique', ['attribute' => 'email']);
                } else {
                    $message = __('validation.unique', ['attribute' => 'username']);
                }

                return $this->sendError($message, 400);
            }

            if ($request->login_type === 'mobile' && $user_data == null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false,
                ];

                return $this->sendError($otp_response);
            }

            if ($request->login_type === 'mobile' && $user_data != null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => true,
                ];

                return $this->sendError($otp_response);
            }

            $name = trim((string) ($claims['name'] ?? ''));
            $nameParts = preg_split('/\s+/', $name, 2) ?: [];
            $firstName = trim((string) ($input['first_name'] ?? $claims['given_name'] ?? ($nameParts[0] ?? '')));
            $lastName = trim((string) ($input['last_name'] ?? $claims['family_name'] ?? ($nameParts[1] ?? '')));

            $input['first_name'] = $firstName !== '' ? $firstName : 'Patient';
            $input['last_name'] = $lastName !== '' ? $lastName : 'User';
            $input['password'] = Hash::make(Str::random(48));
            $user = User::create(collect($input)->only([
                'email',
                'first_name',
                'last_name',
                'user_type',
                'password',
                'player_id',
                'login_type',
                'firebase_uid',
            ])->all());

            $usertype = $user->user_type;

            $user->assignRole($usertype);

            $user->save();

            if ($usertype == "doctor" || $usertype == "vendor") {
                $commission = Commission::first();
                EmployeeCommission::create([
                    'employee_id' => $user->id,
                    'commission_id' => $commission->id,
                ]);
            }


            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('config:cache');


            $user_data = User::where('id', $user->id)->first();

            $message = trans('messages.save_form', ['form' => $input['user_type']]);

            $usertype = $user_data->user_type;

            if ($usertype == "doctor" || $usertype == "vendor") {

                if ($user_data->email_verified_at == null) {

                    return response()->json(['status' => false, 'message' => __('messages.account_not_verify')]);
                }
            }
        }

        if (request('player_id') != null) {
            $user_data->player_id = request('player_id');
            $user_data->save();
        }


        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;

        $socialLogin = new SocialLoginResource($user_data);

        return $this->sendResponse($socialLogin, $message);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()?->delete();
        $user->player_id = null;
        $user->save();

        return response()->json(['status' => true, 'message' => __('messages.user_logout')]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = Password::sendResetLink(
            $request->only('email')
        );
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return $response == Password::RESET_LINK_SENT
                ? response()->json(['message' => __($response), 'status' => true], 200)
                : response()->json(['message' => __($response), 'status' => false], 200);
        }

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($response), 'status' => true], 200)
            : response()->json(['message' => __($response), 'status' => false], 400);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorised')], 401);
        }

        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:191'],
        ]);

        $hashedPassword = $user->password;

        if (! is_string($hashedPassword) || ! Hash::check($data['old_password'], $hashedPassword)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.valid_password'),
            ], 422);
        }

        if (Hash::check($data['new_password'], $hashedPassword)) {
            return response()->json([
                'status' => false,
                'message' => __('messages.same_pass'),
            ], 422);
        }

        $user->forceFill(['password' => Hash::make($data['new_password'])])->save();
        $user->tokens()->delete();
        $success = [
            'api_token' => $user->createToken(setting('app_name'))->plainTextToken,
            'name' => $user->name,
        ];

        return response()->json([
            'status' => true,
            'data' => $success,
            'message' => __('messages.pass_successfull'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorised')], 401);
        }

        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:191'],
            'last_name' => ['sometimes', 'string', 'max:191'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:32'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:32'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'email' => ['sometimes', 'email', 'max:191', 'unique:users,email,'.$user->id],
            'player_id' => ['sometimes', 'nullable', 'string', 'max:512'],
            'profile_image' => ['sometimes', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['profile_image']);
        $user->forceFill($data)->save();

        $user_data = User::find($user->id);
        if ($request->hasFile('profile_image')) {
            storeMediaFile($user_data, $request->file('profile_image'), 'profile_image');
        }

        $user_profile = UserProfile::where('user_id', $user->id)->first();


        if (!$user_profile) {

            $user_profile = new UserProfile();
            $user_profile->user_id = $user->id;
        }


        if ($request->has('expert')) {
            $user_profile->expert = $request->expert;
        }
        if ($request->has('about_self')) {
            $user_profile->about_self = $request->about_self;
        }
        if ($request->has('facebook_link')) {
            $user_profile->facebook_link = $request->facebook_link;
        }
        if ($request->has('instagram_link')) {
            $user_profile->instagram_link = $request->instagram_link;
        }
        if ($request->has('twitter_link')) {
            $user_profile->twitter_link = $request->twitter_link;
        }
        if ($request->has('dribbble_link')) {
            $user_profile->dribbble_link = $request->dribbble_link;
        }

        if ($user_profile != '') {

            $user_profile->save();
        }

        $user_data->save();

        $message = __('messages.profile_update');
        $user_data['user_role'] = $user->getRoleNames();
        $user_data['profile_image'] = $user->profile_image;

        $user_data['about_self'] = $user->profile->about_self ?? null;
        $user_data['expert'] = $user->profile->expert ?? null;
        $user_data['facebook_link'] = $user->profile->facebook_link ?? null;
        $user_data['instagram_link'] = $user->profile->instagram_link ?? null;
        $user_data['twitter_link'] = $user->profile->twitter_link ?? null;
        $user_data['dribbble_link'] = $user->profile->dribbble_link ?? null;

        unset($user_data['roles']);
        unset($user_data['media']);

        return response()->json([
            'status' => true,
            'data' => $user_data,
            'message' => $message,
        ], 200);
    }

    public function userDetails(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => false, 'message' => __('messages.unauthorised')], 401);
        }
        $user['about_self'] = $user->profile->about_self ?? null;
        $user['expert'] = $user->profile->expert ?? null;
        $user['facebook_link'] = $user->profile->facebook_link ?? null;
        $user['instagram_link'] = $user->profile->instagram_link ?? null;
        $user['twitter_link'] = $user->profile->twitter_link ?? null;
        $user['dribbble_link'] = $user->profile->dribbble_link ?? null;

        return response()->json(['status' => true, 'data' => $user, 'message' => __('messages.user_details_successfull')]);
    }

    public function deleteAccount(Request $request)
    {
        $user_id = \Auth::user()->id;
        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            $message = __('messages.user_not_found');

            return response()->json([
                'status' => false,
                'message' => $message,
            ], 200);
        }
        $user->forceDelete();
        
        if($user->user_type == 'doctor'){
            $user->doctor()->delete();
        }

        $message = __('messages.delete_account');

        return response()->json([
            'status' => true,
            'message' => $message,
        ], 200);
    }
    public function checkContact(Request $request)
    {
        $contact = (string) $request->validate([
            'contact' => ['required', 'string', 'max:32'],
        ])['contact'];
        
        $cleanContact = preg_replace('/[^0-9+]/', '', $contact);
        $exists = User::where('mobile', $contact)->exists();
        
        // If not found, try with cleaned number
        if (!$exists) {
            $exists = User::where('mobile', $cleanContact)->exists();
        }
        
        // If still not found, try without country code (last 10 digits)
        if (!$exists && strlen($cleanContact) > 10) {
            $lastTenDigits = substr($cleanContact, -10);
            $exists = User::where('mobile', 'LIKE', '%' . $lastTenDigits)->exists();
        }
        
        if ($exists) {
            $message = __('messages.contact_already_taken');
            return response()->json(['status' => 'error', 'message' => $message], 409); // 409 Conflict
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->validate(['email' => ['required', 'email', 'max:191']])['email'];

        // Check if email exists
        $exists = User::where('email', $email)->exists();
        if ($exists) {
            $message = __('messages.email_already_taken');
            return response()->json(['status' => 'error', 'message' => $message], 409); // 409 Conflict
        }

        return response()->json(['status' => 'success'], 200);
    }
}
