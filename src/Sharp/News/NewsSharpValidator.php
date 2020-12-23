<?php

namespace Code16\Gum\Sharp\News;

use Illuminate\Foundation\Http\FormRequest;

class NewsSharpValidator extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required',
            'published_at' => 'required|date',
        ];
    }
}