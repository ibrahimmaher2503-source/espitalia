<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabTestCategoryFormRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $id = $this->route('lab_test_category') ?? $this->id;

        return [
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:1000',
            'description.ar' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('radiology_lab.lbl_category').' '.__('messages.field_is_required'),
            'name.unique' => __('radiology_lab.lbl_category').' '.__('messages.already_exists'),
        ];
    }
}
