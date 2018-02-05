<?php

namespace Code16\Gum\Tests\Feature;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\Feature\Utils\ContentUrlTestHelpers;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentUrlCreationTest extends TestCase
{
    use RefreshDatabase, ContentUrlTestHelpers;

    /** @test */
    function page_url_is_created_on_tile_creation()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section",
            "content_id" => $section->id,
            "content_type" => Section::class,
        ]);

        $this->assertEquals(url("section/page"), url($tile->fresh()->uri));
    }

    /** @test */
    function section_url_is_created_on_tile_creation()
    {
        $baseSection = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $baseSection->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $section = factory(Section::class)->create(["slug" => "sub-section"]);
        $tile = $tilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $section->id, "linkable_type" => Section::class
        ])->toArray());

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/sub-section",
            "content_id" => $section->id,
            "content_type" => Section::class,
        ]);

        $this->assertEquals(url("section/sub-section"), url($tile->fresh()->uri));

        $pageTilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $page = factory(Page::class)->create(["slug" => "page"]);
        $pageTile = $pageTilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/sub-section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertEquals(url("section/sub-section/page"), url($pageTile->fresh()->uri));
    }

    /** @test */
    function pagegroup_and_pages_url_are_created_on_tile_creation()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $page1 = factory(Page::class)->create(["slug" => "page1"]);
        $page2 = factory(Page::class)->create(["slug" => "page2"]);
        $page1->pagegroup()->associate($pagegroup)->save();
        $page2->pagegroup()->associate($pagegroup)->save();

        $tile = $tilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/pagegroup/page1",
            "content_id" => $page1->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/pagegroup/page2",
            "content_id" => $page2->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/pagegroup",
            "content_id" => $pagegroup->id,
            "content_type" => Pagegroup::class,
        ]);

        $this->assertEquals(url("section/pagegroup"), url($tile->fresh()->uri));
    }

    /** @test */
    function page_in_pagegroup_url_is_created_on_page_creation()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $tilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $page = factory(Page::class)->create(["slug" => "page"]);
        $page->pagegroup()->associate($pagegroup)->save();

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/pagegroup/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);
    }

    /** @test */
    function page_url_is_created_on_tile_update()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $tile = $tilebloc->tiles()->create(factory(Tile::class)->make([
            "free_link_url" => "some-link"
        ])->toArray());

        $this->assertEquals(0, ContentUrl::count());

        $page = factory(Page::class)->create(["slug" => "page"]);
        $tile->update([
            "linkable_id" => $page->id,
            "linkable_type" => Page::class
        ]);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section",
            "content_id" => $section->id,
            "content_type" => Section::class,
        ]);

        $this->assertEquals(url("section/page"), url($tile->fresh()->uri));
    }

    /** @test */
    function url_is_not_created_twice_if_already_existing()
    {
        list($section, $page, $tile1) = $this->createSectionWithTileToPage();

        // Add a second Tile leading to the same Page
        $tile2 = $tile1->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertCount(1, ContentUrl::where("uri", "/section/page")->get());

        $this->assertNotNull($tile1->fresh()->uri);
        $this->assertEquals($tile1->uri, $tile2->fresh()->uri);
    }

    /** @test */
    function url_is_not_created_is_tile_not_linked_to_a_content()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $tile = $tileblock->tiles()->create(factory(Tile::class)->make([
            "free_link_url" => "some-link"
        ])->toArray());

        $this->assertEquals(0, ContentUrl::count());
        $this->assertNull($tile->fresh()->uri);
    }

    /** @test */
    function if_the_url_already_exists_the_page_slug_is_suffixed()
    {
        list($section, $page, $tile1) = $this->createSectionWithTileToPage();

        $page2 = factory(Page::class)->create(["slug" => $page->slug]);
        $tile2 = $tile1->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page2->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertNotEquals($tile1->fresh()->uri, url($tile2->fresh()->uri));
    }
}