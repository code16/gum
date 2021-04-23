<?php

namespace Code16\Gum\Sharp\Tiles;

use Code16\Gum\Sharp\Utils\FreeLinkRule;
use Illuminate\Foundation\Http\FormRequest;

class TileblockSharpValidator extends FormRequest
{
    public function rules()
    {
        return [
            "published_at" => [
                "date", 
                "nullable",
            ],
            "unpublished_at" => [
                "date", 
                "nullable", 
                "required_if:has_unpublished_date,1", 
                "after_or_equal:published_at",
            ],
            "tiles.*.page_id" => [
                "required_if:tiles.*.link_type,page"
            ],
            "tiles.*.free_link_url" => [
                "required_if:tiles.*.link_type,free",
                new FreeLinkRule()
            ],
            "tiles.*.new_page_title" => [
                "required_if:tiles.*.link_type,new,new_pagegroup"
            ],
            "tiles.*.published_at" => [
                "date", 
                "nullable"
            ],
            "tiles.*.unpublished_at" => [
                "date", 
                "nullable", 
                "after_or_equal:tiles.*.published_at",
            ]
        ];
    }
}