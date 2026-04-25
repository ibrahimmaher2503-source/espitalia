<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        $id = request()->id;
        return [
            'title_ar' => 'nullable|string|max:255|required_without:title_en',
            'title_en' => 'nullable|string|max:255|required_without:title_ar',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
