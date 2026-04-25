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
            'name' => 'required|string|max:255',
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
