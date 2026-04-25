<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PharmacyStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('id') ?? $this->route('staff');
        $pharmacyId = auth()->user()->pharmacy_id;

        return [
            'user_id' => [
                'required',
                'exists:users,id',
                // Prevent duplicate user+pharmacy staff entries
                Rule::unique('pharmacy_staff', 'user_id')
                    ->where('pharmacy_id', $pharmacyId)
                    ->ignore($staffId),
            ],
            'role' => 'required|in:owner,manager,pharmacist,cashier,delivery',
            'license_number' => 'required_if:role,pharmacist|nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'hired_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
