<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'category_id' => 'required|exists:pharmacy_categories,id',
            'sub_category_id' => 'required|exists:pharmacy_categories,id',
            'brand_id' => 'nullable|exists:pharmacy_brands,id',
            'product_type_id' => 'nullable|exists:pharmacy_product_types,id',
            'description' => 'nullable|string',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }
}
