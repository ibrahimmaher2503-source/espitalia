<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location_id' => ['required', 'integer'],
            'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'phone' => ['required', 'string', 'max:50'],
            'alternative_phone' => ['nullable', 'string', 'max:50'],
            'chosen_logistic_zone_id' => ['required', 'integer', 'exists:logistic_zones,id'],
            'shipping_delivery_type' => ['required', 'string', 'max:50'],
            'payment_method' => ['required', 'in:cash,cash_on_delivery'],
            // Retained as an optional compatibility field. The API ignores it
            // and always creates an unpaid order until a verified server-side
            // payment flow exists.
            'payment_status' => ['nullable', 'string', 'max:30'],
            'tips' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
