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
            'address_id' => 'nullable|integer|exists:addresses,id',
            'delivery_address' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash_on_delivery',
            'delivery_type' => 'nullable|in:delivery,pickup',
            'delivery_notes' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:50',
            'prescription_id' => 'nullable|integer|exists:pharmacy_prescriptions,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('delivery_type', 'delivery') !== 'delivery') {
                return;
            }

            if (! $this->filled('address_id') && blank(trim((string) $this->input('delivery_address')))) {
                $validator->errors()->add('address_id', __('pharmacy.msg_address_required'));
            }
        });
    }
}
