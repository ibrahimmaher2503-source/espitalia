<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacilitySlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => __('radiology_lab.past_date'),
            'date.date_format' => __('validation.date_format', ['format' => 'Y-m-d']),
            'date.after_or_equal' => __('radiology_lab.past_date'),
        ];
    }
}
