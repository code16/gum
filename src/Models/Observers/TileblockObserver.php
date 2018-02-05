<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\Tileblock;

class TileblockObserver
{

    /**
     * @param Tileblock $tileblock
     */
    public function deleting(Tileblock $tileblock)
    {
        $tileblock->tiles->each->delete();
    }
}