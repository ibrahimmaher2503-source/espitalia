<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LabTestFormRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $id = $this->route('lab_test') ?? $this->id;

        return [
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:50|unique:lab_tests,code,'.$id.',id,deleted_at,NULL',
            'category_id' => 'required|exists:lab_test_categories,id',
            'department' => 'required|in:laboratory,radiology',
            'sample_type' => 'nullable|in:blood,urine,stool,tissue,imaging,swab,other',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:2000',
            'description.ar' => 'nullable|string|max:2000',
            'preparation_instructions' => 'nullable|array',
            'preparation_instructions.en' => 'nullable|string|max:2000',
            'preparation_instructions.ar' => 'nullable|string|max:2000',
            'default_price' => 'required|numeric|min:0',
            'turnaround_time' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('radiology_lab.lbl_test_name').' '.__('messages.field_is_required'),
            'code.required' => __('radiology_lab.lbl_test_code').' '.__('messages.field_is_required'),
            'code.unique' => __('radiology_lab.lbl_test_code').' '.__('messages.already_exists'),
            'category_id.required' => __('radiology_lab.lbl_category').' '.__('messages.field_is_required'),
            'category_id.exists' => __('radiology_lab.lbl_category').' '.__('messages.not_found'),
            'department.required' => __('radiology_lab.lbl_department').' '.__('messages.field_is_required'),
            'default_price.required' => __('radiology_lab.lbl_default_price').' '.__('messages.field_is_required'),
            'default_price.numeric' => __('radiology_lab.lbl_default_price').' '.__('messages.must_be_numeric'),
        ];
    }
}
