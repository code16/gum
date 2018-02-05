<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Sharp\Utils\SlugRule;
use Illuminate\Foundation\Http\FormRequest;

class PagegroupSharpValidator extends FormRequest
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
            "slug" => new SlugRule()
        ];
    }
}