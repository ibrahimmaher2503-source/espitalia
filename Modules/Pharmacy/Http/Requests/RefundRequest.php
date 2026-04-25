<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Patient request refund
        if ($this->isMethod('POST') && ! $this->has('admin_notes')) {
            return [
                'reason' => 'required|string|max:2000',
                'refund_type' => 'required|in:full,partial',
                'refund_amount' => 'required_if:refund_type,partial|nullable|numeric|min:0.01',
                'refund_method' => 'required|in:original_method,wallet,manual',
            ];
        }

        // Admin/Pharmacy process refund
        return [
            'status' => 'required|in:approved,declined,processed',
            'admin_notes' => 'nullable|string|max:2000',
            'refund_amount' => 'nullable|numeric|min:0',
        ];
    }
}
