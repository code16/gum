<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\SlugRule;
use Code16\Sharp\Http\WithSharpContext;
use Illuminate\Foundation\Http\FormRequest;

class SectionSharpValidator extends FormRequest
{
    use WithSharpContext;

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
        $rules = [
            "title" => "required",
            "slug" => new SlugRule()
        ];

        $styles = config("gum.styles" . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() : ""));

        if($styles) {
            $rules["style_key"] = [
                "required",
                "in:" . implode(",", array_keys($styles))
            ];
        }

        return $rules;
    }
}