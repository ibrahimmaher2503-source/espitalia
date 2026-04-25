<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('commission_type') === 'percentage' && $value > 100) {
                        $fail('Commission value cannot exceed 100 for percentage type.');
                    }
                },
            ],
            'status' => 'nullable|boolean',
        ];
    }
}
