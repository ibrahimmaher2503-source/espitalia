<?php

namespace Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:pharmacy_products,id',
            'price' => 'required|numeric|min:0.01',
            'stock_qty' => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ];
    }
}
