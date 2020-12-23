<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Sidepanel;
use Code16\Sharp\EntityList\Commands\ReorderHandler;

class SidepanelSharpReorderHandler implements ReorderHandler
{

    function reorder(array $ids): void
    {
        Sidepanel::whereIn("id", $ids)
            ->get()
            ->each(function(Sidepanel $sidepanel) use($ids) {
                $sidepanel->update([
                    "order" => array_search($sidepanel->id, $ids) + 1
                ]);
            });
    }
}