<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'address_id' => 'required_if:delivery_type,delivery|nullable|exists:addresses,id',
            'payment_method' => 'required|in:cash_on_delivery,online',
            'delivery_type' => 'nullable|in:delivery,pickup',
            'delivery_notes' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:50',
            'prescription_id' => 'nullable|integer|exists:pharmacy_prescriptions,id',
        ];
    }
}
