<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PharmacyBrandRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $brandId = $this->route('pharmacy_brand.id') ?? $this->route('pharmacy_brand');

        return [
            'name' => 'required|string|max:255|unique:pharmacy_brands,name,'.$brandId,
            'is_active' => 'nullable|boolean',
        ];
    }
}
