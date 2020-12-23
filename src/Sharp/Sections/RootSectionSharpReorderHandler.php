<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Sharp\EntityList\Commands\ReorderHandler;

class RootSectionSharpReorderHandler implements ReorderHandler
{

    function reorder(array $ids): void
    {
        Section::whereIn("id", $ids)
            ->get()
            ->each(function(Section $section) use($ids) {
                $section->update([
                    "root_menu_order" => array_search($section->id, $ids) + 1
                ]);
            });
    }
}