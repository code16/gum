<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\SlugRule;
use Illuminate\Foundation\Http\FormRequest;

class SectionSharpValidator extends FormRequest
{
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