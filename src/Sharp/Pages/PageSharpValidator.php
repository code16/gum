<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Sharp\Utils\SlugRule;
use Illuminate\Foundation\Http\FormRequest;

class PageSharpValidator extends FormRequest
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
            "title" => "required",
            "body_text" => "required",
            "visual" => "required",
            "slug" => new SlugRule()
        ];
    }
}