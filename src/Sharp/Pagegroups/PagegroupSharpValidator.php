<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Sharp\Utils\SlugRule;
use Illuminate\Foundation\Http\FormRequest;

class PagegroupSharpValidator extends FormRequest
{
    public function rules()
    {
        return [
            "title" => "required",
            "slug" => new SlugRule()
        ];
    }
}