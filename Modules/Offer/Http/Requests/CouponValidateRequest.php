<?php

namespace Modules\Offer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponValidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50',
            'service_id' => 'required|integer|exists:clinics_services,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'clinic_id' => 'nullable|integer|exists:clinic,id',
            'service_charge' => 'required|numeric|min:0',
            'channel' => 'nullable|in:web,mobile,reception',
            'payment_method' => 'nullable|in:cash,online,advance',
        ];
    }
}
