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

class ContentUrlDeletionTest extends TestCase
{
    use RefreshDatabase, ContentUrlTestHelpers;

    /** @test */
    function old_page_url_is_removed_on_tile_update()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $this->assertEquals(2, ContentUrl::count());

        $tile->update([
            "free_link_url" => "some/link",
            "linkable_id" => null,
            "linkable_type" => null
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertNull($tile->fresh()->uri);
    }

    /** @test */
    function old_page_url_is_not_removed_on_tile_update_if_another_tile_use_it()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        // Another Tile leading to same Page
        $tile2 = $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertEquals(2, ContentUrl::count());

        $tile->update([
            "free_link_url" => "some/link",
            "linkable_id" => null,
            "linkable_type" => null
        ]);

        $this->assertDatabaseHas("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertNull($tile->fresh()->uri);
        $this->assertNotNull($tile2->fresh()->uri);
    }

    /** @test */
    function old_page_url_is_removed_and_new_one_is_created_on_tile_update()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $page2 = factory(Page::class)->create(["slug" => "page2"]);
        $tile->update([
            "linkable_id" => $page2->id,
            "linkable_type" => Page::class
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseHas("content_urls", [
            "content_id" => $page2->id,
            "content_type" => Page::class,
        ]);

        $this->assertEquals(url("section/page2"), url($tile->fresh()->uri));
    }

    /** @test */
    function url_is_deleted_on_tile_deletion()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $tile->delete();

        $this->assertDatabaseMissing("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);
    }

    /** @test */
    function url_is_deleted_on_tileblock_deletion()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $tile->tileblock->delete();

        $this->assertNull(Tile::find($tile->id));

        $this->assertDatabaseMissing("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);
    }

    /** @test */
    function url_is_not_deleted_on_tile_deletion_if_used_elsewhere()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        // Another Tile leading to same Page
        $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        $this->assertCount(1, $page->fresh()->urls);

        $tile->delete();

        $this->assertCount(1, $page->fresh()->urls);

        $this->assertDatabaseHas("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);
    }

    /** @test */
    function url_is_deleted_on_linked_page_deletion()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $page->fresh()->delete();

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertNull($tile->fresh()->uri);
    }

    /** @test */
    function urls_are_deleted_on_linked_section_deletion()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $section2 = factory(Section::class)->create(["slug" => "section2"]);
        $tileblock2 = $section2->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $page2 = factory(Page::class)->create(["slug" => "page2"]);

        // section/section2
        $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $section2->id, "linkable_type" => Section::class
        ])->toArray());

        // section/section2/page2
        $tileToPage2 = $tileblock2->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page2->id, "linkable_type" => Page::class
        ])->toArray());

        $section2->delete();

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $section2->id,
            "content_type" => Section::class,
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page2->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseMissing("tiles", [
            "id" => $tileToPage2->id
        ]);

        $this->assertDatabaseMissing("tileblocks", [
            "id" => $tileblock2->id
        ]);

        $this->assertEmpty($page2->fresh()->urls);
    }

    /** @test */
    function urls_are_deleted_on_section_deletion()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $section->delete();

        $this->assertDatabaseMissing("content_urls", [
            "uri" => "/section/page",
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "uri" => "/section",
            "content_id" => $section->id,
            "content_type" => Section::class,
        ]);

        $this->assertNull(Tile::find($tile->id));
        $this->assertNull(Tileblock::find($tile->tileblock_id));
    }

    /** @test */
    function urls_are_deleted_on_pagegroup_deletion()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $tilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $page = factory(Page::class)->create(["slug" => "page"]);
        $page->pagegroup()->associate($pagegroup)->save();

        $page2 = factory(Page::class)->create(["slug" => "page2"]);
        $page2->pagegroup()->associate($pagegroup)->save();

        $pagegroup->delete();

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page2->id,
            "content_type" => Page::class,
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $pagegroup->id,
            "content_type" => Pagegroup::class,
        ]);

        $this->assertDatabaseMissing("pages", [
            "id" => $page->id
        ]);
    }

    /** @test */
    function page_in_pagegroup_url_is_deleted_on_quitting_pagegroup()
    {
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tilebloc = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $tilebloc->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $page = factory(Page::class)->create(["slug" => "page"]);
        $page->pagegroup()->associate($pagegroup)->save();

        $page->fresh()->update([
            "pagegroup_id" => null
        ]);

        $this->assertDatabaseMissing("content_urls", [
            "content_id" => $page->id,
            "content_type" => Page::class,
        ]);

        $this->assertEmpty($page->fresh()->urls);
    }
}