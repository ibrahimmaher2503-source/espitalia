<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_ar' => 'nullable|string|max:255|required_without:name_en',
            'name_en' => 'nullable|string|max:255|required_without:name_ar',
            'short_description_ar' => 'nullable|string',
            'short_description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'category_ids' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'stock.min' => 'Need to add 1 stock minimun.',
            'price.min' => 'Need to add 1 price minimun.',
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
