<?php

namespace Modules\RadiologyLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestResultFormRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $orderId = $this->route('id');

        return [
            'results' => 'required|array|min:1',
            'results.*.item_id' => [
                'required',
                'exists:test_order_items,id',
                function ($attribute, $value, $fail) use ($orderId) {
                    $exists = \Modules\RadiologyLab\Models\TestOrderItem::where('id', $value)
                        ->where('test_order_id', $orderId)
                        ->exists();
                    if (! $exists) {
                        $fail(__('radiology_lab.item_does_not_belong_to_order'));
                    }
                },
            ],
            'results.*.result_value' => 'required|string|max:500',
            'results.*.result_unit' => 'nullable|string|max:50',
            'results.*.reference_range' => 'nullable|string|max:100',
            'results.*.result_status' => 'nullable|in:normal,abnormal,critical',
            'results.*.result_notes' => 'nullable|string|max:1000',
            'results.*.change_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'results.required' => __('radiology_lab.lbl_enter_results').' '.__('messages.field_is_required'),
            'results.*.item_id.required' => __('messages.field_is_required'),
            'results.*.item_id.exists' => __('messages.not_found'),
            'results.*.result_value.required' => __('radiology_lab.lbl_result_value').' '.__('messages.field_is_required'),
        ];
    }
}
