<?php

namespace Modules\DoctorVisit\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DoctorVisitStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('user');
    }

    public function rules(): array
    {
        return [
            'visit_reason'        => ['required', 'string', 'max:1000'],
            'preferred_date'      => ['required', 'date', 'after_or_equal:today'],
            'contact_phone'       => ['required', 'string', 'max:20'],
            'preferred_doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'additional_notes'    => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
