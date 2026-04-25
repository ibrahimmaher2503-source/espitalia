<?php

namespace Modules\FAQ\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FAQRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question_ar' => 'nullable|string|max:255|required_without:question_en',
            'question_en' => 'nullable|string|max:255|required_without:question_ar',
            'answer_ar' => 'nullable|string|required_without:answer_en',
            'answer_en' => 'nullable|string|required_without:answer_ar',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',

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
