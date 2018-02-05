<?php

namespace Code16\Gum\Tests\Feature\Utils;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;

trait ContentUrlTestHelpers
{
    /**
     * @return array
     */
    protected function createSectionWithTileToPage(): array
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $page = factory(Page::class)->create(["slug" => "page"]);
        $tile = $tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        return [$section, $page, $tile];
    }
}