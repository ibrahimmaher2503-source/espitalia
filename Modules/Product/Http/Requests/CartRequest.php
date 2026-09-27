<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cart_id' => ['sometimes', 'integer'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variation_id' => ['required', 'integer', 'exists:product_variations,id'],
            'qty' => ['required', 'integer', 'min:1'],
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
