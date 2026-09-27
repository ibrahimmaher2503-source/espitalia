<?php

namespace Modules\Frontend\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Hash;
use App\Http\Requests\Auth\LoginRequest;
use Modules\Wallet\Models\WalletHistory;
use Modules\Wallet\Transformers\WalletHistoryResource;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Modules\World\Models\Country;
use Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\RateLimiter;


class UserController extends Controller
{
    public function login(Request $request)
    {
        $redirect_to = route('frontend.index');

        return view('frontend::auth.login', ['redirect_to' => $redirect_to]);
    }

    public function registration()
    {
        return view('frontend::auth.registration');
    }

    public function forgotpassword()
    {
        return view('frontend::auth.forgotpassword');
    }

    public function editProfile()
    {
        $user = Auth::user();
        $countries = Country::select('id', 'name', 'dial_code')->get();

        return view('frontend::edit_profile', compact('user', 'countries'));
    }
    public function updateProfile(Request $request)
    {
        try {
            $rules = [
                'mobile' => [
                    'required',
                    'string',
                    Rule::unique('users', 'mobile')->ignore(Auth::id()),
                ],
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore(Auth::id()),
                ],
                'date_of_birth' => [
                    'required',
                    'date',
                ],
            ];
            $messages = [
                'email.required' => 'Email is required.',
                'email.email' => 'Email must be a valid email address.',
                'email.unique' => 'This email is already in use.',
                'mobile.required' => 'Mobile number is required.',
                'mobile.unique' => 'This mobile number is already in use.',
                'date_of_birth.required' => 'Date of Birth is required.',
            ];

            $validatedData = $request->validate($rules, $messages);

            $user = Auth::user();

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $validatedData['email'];
            $user->mobile = $request->mobile;
            $user->date_of_birth = $request->date_of_birth;
            $user->address = $request->address;
            $user->gender = $request->gender;

            if ($request->hasFile('profile_image')) {
                $user->clearMediaCollection('profile_image');

                $user->addMedia($request->file('profile_image'))->toMediaCollection('profile_image');
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Profile update error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    public function updateProfileImage(Request $request)
    {
        try {
            $user = Auth::user();
            // Remove existing profile image if any and upload new one
            if ($request->hasFile('profile_image')) {
                $user->clearMediaCollection('profile_image');
                $user->addMedia($request->file('profile_image'))->toMediaCollection('profile_image');
            }
            return response()->json(['success' => true, 'message' => 'Profile image updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong.']);
        }
    }



    public function accountSetting(Request $request)
    {
        return view('frontend::account_setting');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'max:14',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,14}$/'
            ],
        ], [
            'old_password.required' => __('messages.validation_old_password_required'),
            'new_password.required' => __('messages.validation_new_password_required'),
            'new_password.min' => __('messages.validation_new_password_min'),
            'new_password.max' => __('messages.validation_new_password_max'),
            'new_password.regex' => __('messages.validation_new_password_regex'),
        ]);

        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['old_password' => 'The old password is incorrect.'],
            ], 422);
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Send change password notification
        sendNotification([
            'notification_type' => 'change_password',
            'user_id' => auth()->user()->id,
            'user_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'notification_group' => 'change_password',
            'id' => auth()->user()->id,
        ]);

        return response()->json(['success' => true]);
    }



    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json([
                'error' => 'The provided password is incorrect.',
            ], 422);
        }

        $user = auth()->user();
        // $user->delete();
        $user->forceDelete();

        auth()->logout();

        return response()->json([
            'success' => 'Your account has been deleted.',
        ]);
    }


    public function walletHistory(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $user_id = $request->user_id ?? auth()->user()->id;

        $wallet_history = WalletHistory::with('wallet')
            ->where('user_id', $user_id)
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        $data = WalletHistoryResource::collection($wallet_history);

        return view('frontend::wallet_history', compact('wallet_history', 'data', 'perPage'));
    }

    public function walletHistoryIndexData(Request $request)
    {
        $userId = $request->user_id ?? auth()->user()->id;
        $walletHistory = WalletHistory::with('wallet')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        return DataTables::of($walletHistory)
            ->addColumn('card', function ($history) {
                return view('frontend::components.card.wallet_card', compact('history'))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }

    public function loginstore(LoginRequest $request)
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

        if ($requestedUserType != $usertype && $usertype !== 'user' || $requestedUserType !== 'user') {
            return response()->json(['status' => false, 'message' => __('messages.access_denied_role')]);
        }

        $credentials = $request->only('email', 'password');

        $request->ensureIsNotRateLimited();
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($request->throttleKey());
            $user = Auth::user();

            if ($user->is_banned == 1 || $user->status == 0) {
                Auth::logout();
                return response()->json(['status' => false, 'message' => __('messages.login_error')]);
            }

            $request->session()->regenerate();

            return $this->sendResponse('', __('messages.user_login'));
        } else {
            RateLimiter::hit($request->throttleKey(), 60);
            return $this->sendError(__('messages.not_matched'), ['error' => __('messages.unauthorised')], 200);
        }
    }

    // Redirect to Google
    public function redirectToGoogle()
    {

        return Socialite::driver('google')->redirect();
    }

    // Handle Google Callback
    public function handleGoogleCallback(Request $request)
    {

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {

                $fullName = $googleUser->getName();

                $nameParts = explode(' ', $fullName);

                $firstName = isset($nameParts[0]) ? $nameParts[0] : ''; // First part of the name
                $lastName = isset($nameParts[1]) ? $nameParts[1] : $firstName;  // Second part as last name


                $data = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(8)),
                    'user_type' => 'user',
                    'login_type' => 'google'
                ];

                $user = User::create($data);

                $request->session()->regenerate();

                $user->createOrUpdateProfileWithAvatar();

                $user->assignRole($data['user_type']);

                $user->save();
            }

            if ($user->login_type == 'google') {
                $current_device = $request->has('device_id') ? $request->device_id : $request->getClientIp();
                $response = $this->CheckDeviceLimit($user, $current_device);

                if (isset($response['error'])) {
                    return Redirect::to('/user-login')->with('error', $response['error']);
                }

                $this->setDevice($user, $request);
                $user1 = Auth::login($user);
            } else {
                $user = Auth::user();
                Auth::logout();
                $this->removeDevice($user, $request);
                return Redirect::to('/user-login')->with('error', 'Something went wrong! During login');
            }

            return redirect()->intended('/'); // Redirect to intended page
        } catch (\Exception $e) {
            return Redirect::to('/user-login')->with('error', 'Something went wrong!');
        }
    }
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();

                    if ($user && $user->user_type !== 'user') {
                        $fail('The user type must be "user" to request a password reset.');
                    }
                }
            ],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'No user found with this email address.']);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    public function create(Request $request)
    {
        return view('frontend::auth.reset-password', ['request' => $request]);
    }

    public function storepassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'max:12', // <- Add this plain string rule for maximum length
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login-page')->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }


    public function userNotifications()
    {
        $module_name = 'notifications';
        $module_name_singular = 'notification';

        $user = auth()->user();

        if (count($user->unreadNotifications) > 0) {
            $user->unreadNotifications->markAsRead();
        }
        $perPage = request('per_page', 10);
        $$module_name = auth()->user()->notifications()->paginate($perPage);
        $unread_notifications_count = auth()->user()->unreadNotifications()->count();

        $notifications_count = 0;

        return view(
            "frontend::notification_list",
            compact('module_name', "$module_name", 'module_name_singular', 'unread_notifications_count', 'notifications_count')
        );
    }

    public function userNotifications_indexData(Request $request)
    {
        $module_name = 'notifications';
        $module_name_singular = 'notification';

        $user = auth()->user();

        if ($user->unreadNotifications->count() > 0) {
            $user->unreadNotifications->markAsRead();
        }

        $notifications = $user->notifications()->get();
        $unread_notifications_count = $user->unreadNotifications->count();
        $notifications_count = $notifications->count(); // Total count of all notifications
        // dd($notifications);

        return DataTables::of($notifications)
            ->addColumn('card', function ($notification) use (
                $module_name,
                $module_name_singular,
                $unread_notifications_count,
                $notifications_count,
            ) {
                return view('frontend::components.card.notification_card', compact(
                    'module_name',
                    'module_name_singular',
                    'notification',
                    'unread_notifications_count',
                    'notifications_count'
                ))->render();
            })
            ->rawColumns(['card'])
            ->make(true);
    }
}
