<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Sharp\Utils\SlugRule;
use Code16\Sharp\Http\WithSharpFormContext;
use Illuminate\Foundation\Http\FormRequest;

class SectionSharpValidator extends FormRequest
{
    use WithSharpFormContext;

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
            "style_key" => "required",
            "slug" => new SlugRule()
        ];
    }
}