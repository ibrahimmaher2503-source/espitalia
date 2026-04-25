<?php

namespace Modules\IntensiveCare\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IcuPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:cash,bank_transfer',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
