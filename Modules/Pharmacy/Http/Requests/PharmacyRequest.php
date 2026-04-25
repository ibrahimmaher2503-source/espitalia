<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'name_ar' => 'required_without:name_en|nullable|string|max:255',
            'name_en' => 'required_without:name_ar|nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'delivery_fee' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'commission_id' => 'nullable|exists:pharmacy_commissions,id',
        ];
    }
}
