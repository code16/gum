<?php

namespace Code16\Gum\Tests\Feature\Models;

use Carbon\Carbon;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\TestCase;

class PageTest extends TestCase
{
    /** @test */
    function we_can_find_a_page_uri()
    {
        $homePage = factory(Page::class)->create(["slug" => ""]);
        $sectionPage = factory(Page::class)->create(["slug" => "my-section"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $homePage->id
                    ])
                    ->id,
                "page_id" => $sectionPage->id
            ]);

        $page = factory(Page::class)->create(["slug" => "my-page"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $sectionPage->id
                    ])
                    ->id,
                "page_id" => $page->id
            ]);

        $this->assertEquals("/my-section/my-page", $page->findUri());
    }

    /** @test */
    function we_cant_find_a_page_uri_through_an_hidden_tile()
    {
        $homePage = factory(Page::class)->create(["slug" => ""]);
        $sectionPage = factory(Page::class)->create(["slug" => "my-section"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $homePage->id
                    ])
                    ->id,
                "visibility" => "OFFLINE",
                "page_id" => $sectionPage->id
            ]);

        $page = factory(Page::class)->create(["slug" => "my-page"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $sectionPage->id
                    ])
                    ->id,
                "page_id" => $page->id
            ]);

        $this->assertNull($page->findUri());
    }

    /** @test */
    function page_uri_is_cached_if_configured()
    {
        config()->set("gum.cache", [
            "enabled" => true,
            "ttl" => 20 // seconds
        ]);
        
        $homePage = factory(Page::class)->create(["slug" => ""]);
        $sectionPage = factory(Page::class)->create(["slug" => "my-section-old"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $homePage->id
                    ])
                    ->id,
                "page_id" => $sectionPage->id
            ]);

        $page = factory(Page::class)->create(["slug" => "my-page"]);
        factory(Tile::class)
            ->create([
                "tileblock_id" => factory(Tileblock::class)
                    ->create([
                        "page_id" => $sectionPage->id
                    ])
                    ->id,
                "page_id" => $page->id
            ]);

        $this->assertEquals("/my-section-old/my-page", $page->findUri());

        $sectionPage->update(["slug" => "my-section-new"]);
        $this->assertEquals("/my-section-old/my-page", $page->findUri());
        
        Carbon::setTestNow(now()->addMinute());
        $this->assertEquals("/my-section-new/my-page", $page->findUri());
    }
}