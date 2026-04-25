<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:governorate_cities,id',
            'delivery_fee' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'estimated_delivery_minutes' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}
