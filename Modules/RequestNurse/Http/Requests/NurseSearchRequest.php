<?php

namespace Modules\RequestNurse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NurseSearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'governorate_id' => 'nullable|integer|exists:governorates,id',
            'city_id' => 'nullable|integer|exists:governorate_cities,id',
            'specialty' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:male,female',
            'availability' => 'nullable|string|in:available,busy,off_duty',
        ];
    }
}
