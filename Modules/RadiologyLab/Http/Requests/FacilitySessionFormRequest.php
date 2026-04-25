<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilitySessionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $sessionId = $this->input('id');

        return [
            'facility_type' => 'required|in:lab,radiology',
            'facility_id' => 'required|integer',
            'day' => [
                'required',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                // VL-05: ignore soft-deleted rows so restoring doesn't double-book
                Rule::unique('facility_sessions')
                    ->where('facility_type', $this->facility_type)
                    ->where('facility_id', $this->facility_id)
                    ->whereNull('deleted_at')
                    ->ignore($sessionId),
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'breaks' => 'nullable|array',
            'breaks.*.start_break' => 'required_with:breaks|date_format:H:i',
            'breaks.*.end_break' => 'required_with:breaks|date_format:H:i|after:breaks.*.start_break',
            'is_holiday' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'day.unique' => __('radiology_lab.session_day_unique'),
            'end_time.after' => __('radiology_lab.session_end_after_start'),
        ];
    }
}
