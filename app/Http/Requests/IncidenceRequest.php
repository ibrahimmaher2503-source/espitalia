<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IncidenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        switch (strtolower($this->getMethod())) {
            case 'post':
                return [
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:5000',
                    'country_code' => ['required', 'string', 'max:8', 'regex:/^\\+?[0-9]+$/'],
                    'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9][0-9 .()-]+$/'],
                    'email' => 'required|email:filter|max:255',
                    'file_url' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ];
                break;
            case 'put':
            case 'patch':

                return [
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:5000',
                    'country_code' => ['required', 'string', 'max:8', 'regex:/^\\+?[0-9]+$/'],
                    'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9][0-9 .()-]+$/'],
                    'email' => 'required|email:filter|max:255',
                    'file_url' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ];
                break;

            default:
                // code...
                break;
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'status' => false,
            'message' => $validator->errors()->first(),
            'all_message' => $validator->errors(),
        ];

        if (request()->wantsJson() || request()->is('api/*')) {
            throw new HttpResponseException(response()->json($data, 422));
        }

        throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
    }
}
