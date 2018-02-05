<?php

namespace Code16\Gum\Sharp\Tiles;

use Code16\Gum\Models\Tileblock;

class AcaciaTileblockSharpList extends TileblockSharpList
{

    /**
     * @param string $layout
     * @param Tileblock $tileblock
     * @return string
     */
    protected function layoutCustomTransformer(string $layout, Tileblock $tileblock)
    {
        $layout = "?";

        if($this->isCheckerboard($tileblock)) {
            $layout = "Damier";
        }

        return $layout . ($tileblock->layout_variant ? "<br><em>{$tileblock->layout_variant}</em>" : "");;
    }

    /**
     * @param $content
     * @param Tileblock $tileblock
     * @return mixed
     */
    protected function contentCustomTransformer($content, Tileblock $tileblock)
    {
        return "";
    }

    /**
     * @param Tileblock $tileblock
     * @return bool
     */
    protected function isCheckerboard(Tileblock $tileblock)
    {
        return $tileblock->layout == "checkerboard";
    }
}