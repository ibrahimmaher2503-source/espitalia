<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RadiologyCenterFormRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            // VL-04: prevent duplicate center names (soft-delete aware)
            'name_ar' => 'required|string|max:255|unique:radiology_centers,name_ar,' . ($id ?? 'NULL') . ',id,deleted_at,NULL',
            'name_en' => 'required|string|max:255|unique:radiology_centers,name_en,' . ($id ?? 'NULL') . ',id,deleted_at,NULL',
            'scan_type' => 'nullable|string|max:255',
            'governorate_id' => 'nullable|integer|exists:governorates,id',
            'city_id' => 'nullable|integer|exists:governorate_cities,id',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name_ar.required' => __('radiology_lab.lbl_name_ar') . ' ' . __('messages.field_is_required'),
            'name_en.required' => __('radiology_lab.lbl_name_en') . ' ' . __('messages.field_is_required'),
        ];
    }
}
