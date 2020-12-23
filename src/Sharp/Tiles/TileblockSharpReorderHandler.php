<?php

namespace Code16\Gum\Sharp\Tiles;

use Code16\Gum\Models\Tileblock;
use Code16\Sharp\EntityList\Commands\ReorderHandler;

class TileblockSharpReorderHandler implements ReorderHandler
{

    function reorder(array $ids): void
    {
        Tileblock::whereIn("id", $ids)
            ->get()
            ->each(function(Tileblock $tileblock) use($ids) {
                $tileblock->update([
                    "order" => array_search($tileblock->id, $ids) + 1
                ]);
            });
    }
}