<?php

namespace Modules\Branch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $branchId = $this->route('id') ?? $this->route('branch');

        $rules = [
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'type' => 'required|in:clinic,pharmacy,lab,radiology_center,hospital,multi',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'governorate_id' => 'nullable|exists:governorates,id',
            'city_id' => 'nullable|exists:governorate_cities,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:2000',
            'description.ar' => 'nullable|string|max:2000',
            'operating_hours' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'manager_id' => 'nullable|exists:users,id',
            'license_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'branch_image' => 'nullable|image|max:2048',
        ];

        return $rules;
    }
}
