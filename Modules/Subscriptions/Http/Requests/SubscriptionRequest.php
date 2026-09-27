<?php

namespace Modules\Subscriptions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'identifier' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['nullable', 'string', 'max:32'],
            'payment_type' => ['nullable', 'string', 'max:64'],
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
