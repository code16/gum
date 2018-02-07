<?php

namespace Code16\Gum\Sharp\News;

use Illuminate\Foundation\Http\FormRequest;

class NewsSharpValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'published_at' => 'required|date',
        ];
    }
}