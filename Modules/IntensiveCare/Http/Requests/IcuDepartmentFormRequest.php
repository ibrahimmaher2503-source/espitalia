<?php

namespace Modules\IntensiveCare\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IcuDepartmentFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('icu_department') ?? $this->id;

        return [
            'hospital_id' => 'required|exists:hospitals,id',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'specialty_type' => 'required|in:cardiac,neurology,pediatric,neonatal,burns,chest,surgical,general',
            'total_beds' => 'required|integer|min:0',
            'available_beds' => 'required|integer|min:0|lte:total_beds',
            'has_ventilator' => 'nullable|boolean',
            'has_oxygen' => 'nullable|boolean',
            'equipment_level' => 'required|in:basic,advanced,full',
            'daily_price' => 'required|numeric|min:0',
            'status' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->name_ar) && empty($this->name_en)) {
                $validator->errors()->add(
                    'name_ar',
                    __('intensive_care.lbl_name_ar').' '.__('messages.or').' '.__('intensive_care.lbl_name_en').' '.__('messages.field_is_required')
                );
                $validator->errors()->add(
                    'name_en',
                    __('intensive_care.lbl_name_ar').' '.__('messages.or').' '.__('intensive_care.lbl_name_en').' '.__('messages.field_is_required')
                );
            }
        });
    }

    public function messages()
    {
        return [
            'hospital_id.required' => __('intensive_care.lbl_hospital_name').' '.__('messages.field_is_required'),
            'hospital_id.exists' => __('intensive_care.lbl_hospital_name').' '.__('messages.not_found'),
            'name_ar.string' => __('intensive_care.lbl_name_ar').' '.__('messages.must_be_string'),
            'name_ar.max' => __('intensive_care.lbl_name_ar').' '.__('messages.max_characters', ['max' => 255]),
            'name_en.string' => __('intensive_care.lbl_name_en').' '.__('messages.must_be_string'),
            'name_en.max' => __('intensive_care.lbl_name_en').' '.__('messages.max_characters', ['max' => 255]),
            'description_ar.max' => __('intensive_care.lbl_description_ar').' '.__('messages.max_characters', ['max' => 5000]),
            'description_en.max' => __('intensive_care.lbl_description_en').' '.__('messages.max_characters', ['max' => 5000]),
            'specialty_type.required' => __('intensive_care.lbl_specialty_type').' '.__('messages.field_is_required'),
            'specialty_type.in' => __('intensive_care.lbl_specialty_type').' '.__('messages.is_invalid'),
            'total_beds.required' => __('intensive_care.lbl_total_beds').' '.__('messages.field_is_required'),
            'available_beds.required' => __('intensive_care.lbl_available_beds').' '.__('messages.field_is_required'),
            'available_beds.lte' => __('intensive_care.lbl_available_beds').' '.__('messages.must_not_exceed').' '.__('intensive_care.lbl_total_beds'),
            'equipment_level.required' => __('intensive_care.lbl_equipment_level').' '.__('messages.field_is_required'),
            'daily_price.required' => __('intensive_care.lbl_daily_price').' '.__('messages.field_is_required'),
            'daily_price.numeric' => __('intensive_care.lbl_daily_price').' '.__('messages.must_be_numeric'),
        ];
    }
}
