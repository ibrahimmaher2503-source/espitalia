<?php

namespace Modules\IntensiveCare\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HospitalFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('hospital') ?? $this->id;

        return [
            'name_ar' => 'nullable|string|max:255|required_without:name_en',
            'name_en' => 'nullable|string|max:255|required_without:name_ar',
            'description_ar' => 'nullable|string|max:5000',
            'description_en' => 'nullable|string|max:5000',
            'address_ar' => 'nullable|string|max:255',
            'address_en' => 'nullable|string|max:255',
            'hospital_type' => 'required|in:government,private,military,university',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:governorate_cities,id',
            'commission_id' => 'nullable|exists:icu_commissions,id',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'license_number' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accepted_insurance' => 'nullable|array',
            'accepted_insurance.*' => 'string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name_ar.required_without' => __('intensive_care.lbl_name_ar').' '.__('messages.field_is_required').' '.__('messages.when').' '.__('intensive_care.lbl_name_en').' '.__('messages.is_empty'),
            'name_en.required_without' => __('intensive_care.lbl_name_en').' '.__('messages.field_is_required').' '.__('messages.when').' '.__('intensive_care.lbl_name_ar').' '.__('messages.is_empty'),
            'hospital_type.required' => __('intensive_care.lbl_hospital_type').' '.__('messages.field_is_required'),
            'hospital_type.in' => __('intensive_care.lbl_hospital_type').' '.__('messages.is_invalid'),
            'phone.required' => __('intensive_care.lbl_phone').' '.__('messages.field_is_required'),
            'governorate_id.exists' => __('intensive_care.lbl_governorate').' '.__('messages.is_invalid'),
            'city_id.exists' => __('intensive_care.lbl_city').' '.__('messages.is_invalid'),
            'commission_id.exists' => __('intensive_care.lbl_commission').' '.__('messages.is_invalid'),
        ];
    }
}
