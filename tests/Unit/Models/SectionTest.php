<?php

namespace Code16\Gum\Tests\Unit\Models;

use Carbon\Carbon;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\Feature\Utils\ContentUrlTestHelpers;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SectionTest extends TestCase
{
    use RefreshDatabase, ContentUrlTestHelpers;

    /** @test */
    function we_can_get_only_online_tiles()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => factory(Page::class)->create()->id, "linkable_type" => Page::class,
            "visibility" => "OFFLINE"
        ])->toArray());
        $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => factory(Page::class)->create()->id, "linkable_type" => Page::class,
            "published_at" => Carbon::tomorrow()
        ])->toArray());

        $this->assertCount(1, $section->online_tileblocks);
        $this->assertCount(1, $section->online_tileblocks[0]->tiles);
        $this->assertCount(3, $section->tileblocks[0]->tiles);
        $this->assertEquals($tile->id, $section->online_tileblocks[0]->tiles[0]->id);
    }

    /** @test */
    function we_can_get_only_online_tileblocks()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $tileblock2 = $section->tileblocks()->create(factory(Tileblock::class)->make([
            "published_at" => Carbon::tomorrow()
        ])->toArray());
        $tileblock2->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => factory(Page::class)->create()->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertCount(1, $section->online_tileblocks);
        $this->assertCount(2, $section->tileblocks);
    }
}