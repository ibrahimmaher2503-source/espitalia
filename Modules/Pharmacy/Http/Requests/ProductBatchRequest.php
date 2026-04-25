<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        return [
            'inventory_id' => [
                'required',
                Rule::exists('pharmacy_inventory', 'id')->where('pharmacy_id', $pharmacyId),
            ],
            'batch_number' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'manufacturing_date' => 'nullable|date|before:expiry_date',
            'expiry_date' => 'required|date|after:today',
            'purchase_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
