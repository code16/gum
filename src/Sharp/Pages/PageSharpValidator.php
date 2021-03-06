<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Sharp\Utils\SlugRule;
use Illuminate\Foundation\Http\FormRequest;

class PageSharpValidator extends FormRequest
{
    public function rules()
    {
        return [
            "title" => "required",
            "slug" => new SlugRule()
        ];
    }
}