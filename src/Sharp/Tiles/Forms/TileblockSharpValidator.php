<?php

namespace Code16\Gum\Sharp\Tiles\Forms;

use Illuminate\Foundation\Http\FormRequest;

class TileblockSharpValidator extends FormRequest
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
            "published_at" => "date|nullable",
            "unpublished_at" => "date|nullable|required_if:has_unpublished_date,1|after_or_equal:published_at",
            "tiles.*.section" => "required_if:tiles.*.link_type,Code16\Gum\Models\Section",
            "tiles.*.page" => "required_if:tiles.*.link_type,Code16\Gum\Models\Page",
            "tiles.*.pagegroup" => "required_if:tiles.*.link_type,Code16\Gum\Models\Pagegroup",
            "tiles.*.published_at" => "date|nullable",
            "tiles.*.unpublished_at" => "date|nullable|after_or_equal:tiles.*.published_at",
        ];
    }
}