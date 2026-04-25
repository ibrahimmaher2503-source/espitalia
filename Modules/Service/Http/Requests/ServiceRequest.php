<?php

namespace Modules\Service\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceRequest extends FormRequest
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
                    'name_ar' => 'nullable|string|max:255|required_without:name_en',
                    'name_en' => 'nullable|string|max:255|required_without:name_ar',
                    'description_ar' => 'nullable|string',
                    'description_en' => 'nullable|string',
                    'duration_min' => 'required|integer',
                    'category_id' => 'required|integer',
                    'default_price' => 'required',
                    'status' => 'boolean',
                ];
                break;
            case 'put':
            case 'patch':
                return [
                    'name_ar' => 'nullable|string|max:255|required_without:name_en',
                    'name_en' => 'nullable|string|max:255|required_without:name_ar',
                    'description_ar' => 'nullable|string',
                    'description_en' => 'nullable|string',
                    'duration_min' => 'required|integer',
                    'category_id' => 'required|integer',
                    'default_price' => 'required',
                    'status' => 'boolean',
                ];
                break;
        }

        return [];
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
