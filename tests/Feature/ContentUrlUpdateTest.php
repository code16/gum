<?php

namespace Code16\Gum\Tests\Feature;

use Carbon\Carbon;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\Feature\Utils\ContentUrlTestHelpers;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentUrlUpdateTest extends TestCase
{
    use RefreshDatabase, ContentUrlTestHelpers;

    /** @test */
    function if_the_page_slug_is_updated_the_url_is_too()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $page->fresh()->update([
            "slug" => "new-page-slug"
        ]);

        $this->assertEquals(url("section/new-page-slug"), url($tile->fresh()->uri));
    }

    /** @test */
    function if_a_section_slug_is_updated_its_url_and_all_sub_urls_are_too()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $page2 = factory(Page::class)->create(["slug" => "page2"]);
        $tile2 = $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page2->id, "linkable_type" => Page::class
        ])->toArray());

        $section->update([
            "slug" => "new-section-slug"
        ]);

        $this->assertEquals(url("new-section-slug"), url($section->url->uri));
        $this->assertEquals(url("new-section-slug/page"), url($tile->fresh()->uri));
        $this->assertEquals(url("new-section-slug/page2"), url($tile2->fresh()->uri));
    }

    /** @test */
    function if_a_pagegroup_slug_is_updated_its_url_and_all_sub_urls_are_too()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $page1 = factory(Page::class)->create(["slug" => "page1"]);
        $page2 = factory(Page::class)->create(["slug" => "page2"]);
        $page1->pagegroup()->associate($pagegroup)->save();
        $page2->pagegroup()->associate($pagegroup)->save();

        $tile = $tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $pagegroup->update([
            "slug" => "new-pagegroup-slug"
        ]);

        $this->assertEquals(url("section/new-pagegroup-slug"), url($tile->fresh()->uri));
        $this->assertEquals(url("section/new-pagegroup-slug/page1"), url($page1->fresh()->urls->first()->uri));
        $this->assertEquals(url("section/new-pagegroup-slug/page2"), url($page2->fresh()->urls->first()->uri));
    }

    /** @test */
    function page_url_visibility_is_updated_with_tile_visibility()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $this->assertEquals("ONLINE", $tile->contentUrl->visibility);
        $this->assertNull($tile->contentUrl->published_at);

        $tile->update([
            "visibility" => "OFFLINE",
        ]);

        $this->assertEquals("OFFLINE", $tile->contentUrl->fresh()->visibility);

        $tile->update([
            "visibility" => "ONLINE",
            "published_at" => Carbon::tomorrow(),
            "unpublished_at" => Carbon::tomorrow()->addDay(),
        ]);

        $this->assertEquals("ONLINE", $tile->refresh()->contentUrl->visibility);
        $this->assertEquals(Carbon::tomorrow()->timestamp, $tile->contentUrl->published_at->timestamp);
        $this->assertEquals(Carbon::tomorrow()->addDay()->timestamp, $tile->contentUrl->unpublished_at->timestamp);
    }

    /** @test */
    function section_url_visibility_is_not_updated_with_tile_visibility()
    {
        list($section, $page, $pagetile) = $this->createSectionWithTileToPage();

        // Create a "back to section" link on the page
        $tile = $pagetile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $section->id, "linkable_type" => Section::class,
            "visibility" => "OFFLINE"
        ])->toArray());

        $this->assertEquals("ONLINE", $section->fresh()->url->visibility);

        $tile->update([
            "visibility" => "OFFLINE",
        ]);

        $this->assertEquals("ONLINE", $section->fresh()->url->visibility);

        $tile->update([
            "published_at" => Carbon::tomorrow(),
            "unpublished_at" => Carbon::tomorrow()->addDay(),
        ]);

        $this->assertNull($section->fresh()->url->published_at);
        $this->assertNull($section->fresh()->url->unpublished_at);
    }
}