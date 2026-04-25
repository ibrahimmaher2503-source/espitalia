<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class customerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (strtolower($this->getMethod())) {
            case 'post':
                return [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => 'required|string|unique:users,email',
                    'mobile' => 'required|string',
                    'gender' => 'string',
                    'password' => [
                        'required',
                        'string',
                        'min:6',             // must be at least 10 characters in length
                        'regex:/[a-z]/',      // must contain at least one lowercase letter
                        'regex:/[A-Z]/',      // must contain at least one uppercase letter
                        'regex:/[0-9]/',      // must contain at least one digit
                        'regex:/[@$!%*#?&]/', // must contain a special character
                    ],
                ];
                break;
            case 'put':
            case 'patch':
                return [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string',
                    'email' => ['required', 'string', Rule::unique('users', 'email')->ignore($this->id)->whereNull('deleted_at')],
                    'mobile' => 'required|string',
                    'gender' => 'string',
                ];
                break;
            default:
                return [];
                break;
        }
    }

    public function messages()
    {
        return [
            'password.regex' => 'Password must contain at least one uppercase / one lowercase / one number and one symbol.',            
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
