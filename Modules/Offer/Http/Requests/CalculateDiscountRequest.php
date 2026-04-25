<?php

namespace Modules\Offer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|integer|exists:clinics_services,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'clinic_id' => 'nullable|integer|exists:clinic,id',
            'service_charge' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50',
            'channel' => 'nullable|in:web,mobile,reception',
            'payment_method' => 'nullable|in:cash,online,advance',
            'offer_id' => 'nullable|integer|exists:offers,id',
        ];
    }
}
