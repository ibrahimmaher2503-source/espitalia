<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyCategoryRequest extends FormRequest
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
            'parent_id' => 'nullable|exists:pharmacy_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
