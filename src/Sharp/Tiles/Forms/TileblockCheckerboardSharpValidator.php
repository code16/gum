<?php

namespace Code16\Gum\Sharp\Tiles\Forms;

class TileblockCheckerboardSharpValidator extends TileblockSharpValidator
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return parent::rules() + [
            "tiles.*.visual" => "required",
            "tiles.*.title" => "required",
        ];
    }
}