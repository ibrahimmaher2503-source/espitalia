<?php

namespace Modules\RadiologyLab\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\RadiologyLab\Models\Lab;
use Modules\RadiologyLab\Models\RadiologyCenter;
use Modules\RadiologyLab\Services\FacilitySlotService;

class FacilityBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:lab,radiology',
            'lab_id' => 'required_if:type,lab|nullable|exists:labs,id',
            'radiology_center_id' => 'required_if:type,radiology|nullable|exists:radiology_centers,id',
            'scan_type' => 'required_if:type,radiology|nullable|string|max:255',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('validation.required', ['attribute' => __('radiology_lab.lbl_booking_type')]),
            'type.in' => __('validation.in', ['attribute' => __('radiology_lab.lbl_booking_type')]),
            'lab_id.required_if' => __('validation.required', ['attribute' => 'lab_id']),
            'radiology_center_id.required_if' => __('validation.required', ['attribute' => 'radiology_center_id']),
            'booking_date.required' => __('validation.required', ['attribute' => __('radiology_lab.lbl_booking_date')]),
            'booking_date.after_or_equal' => __('radiology_lab.past_date'),
            'booking_time.required' => __('validation.required', ['attribute' => __('radiology_lab.lbl_booking_time')]),
            'booking_time.date_format' => __('validation.date_format', ['format' => 'H:i']),
            'patient_name.required' => __('validation.required', ['attribute' => __('radiology_lab.lbl_patient_name')]),
            'patient_phone.required' => __('validation.required', ['attribute' => __('radiology_lab.lbl_patient_phone')]),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->input('type');
            $facilityId = $type === 'lab' ? $this->input('lab_id') : $this->input('radiology_center_id');
            $date = $this->input('booking_date');
            $time = $this->input('booking_time');

            // Check facility is active
            if ($type === 'lab') {
                $facility = Lab::find($facilityId);
            } else {
                $facility = RadiologyCenter::find($facilityId);
            }

            if (! $facility || ! $facility->status) {
                $v->errors()->add('type', __('radiology_lab.facility_inactive'));

                return;
            }

            // VL-08: if booking today, require the time to be in the future
            try {
                if ($date === now()->toDateString()) {
                    $slotMoment = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
                    if ($slotMoment->lte(now())) {
                        $v->errors()->add('booking_time', __('radiology_lab.slot_unavailable'));
                        return;
                    }
                }
            } catch (\Throwable $e) {
                // Time format errors are already caught by date_format rule
            }

            // Validate that the requested time belongs to the facility's
            // schedule. Whether it is already booked is checked again inside
            // the controller transaction so duplicate attempts receive the
            // API's stable {status:false} response and cannot race.
            $slotService = app(FacilitySlotService::class);
            $slots = $slotService->getAvailableSlots($type, (int) $facilityId, $date);

            $scheduled = collect($slots)->first(fn ($s) => $s['time'] === $time);

            if (! $scheduled) {
                $v->errors()->add('booking_time', __('radiology_lab.slot_unavailable'));
            }
        });
    }
}
