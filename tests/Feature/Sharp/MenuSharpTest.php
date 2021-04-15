<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;

class MenuSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_access_to_sharp_form_menus()
    {
        $this
            ->getSharpForm("menus")
            ->assertOk();
    }

    /** @test */
    function we_can_create_menu_tileblocks()
    {
        $page = factory(Page::class)->create([
            "slug" => ""
        ]);

        $this
            ->withSharpCurrentBreadcrumb(
                [
                    ["list", "pages"],
                    ["show", "pages", $page->id]
                ]
            )
            ->storeSharpForm("menus",
                factory(Tileblock::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this->assertCount(1, Tileblock::all());
    }

    /** @test */
    function we_can_update_tiles_in_menu_tileblocks()
    {
        $tileblockAttributes = factory(Tileblock::class)
            ->create()
            ->getAttributes();

        $tiles = factory(Tile::class, 5)->create([
            "tileblock_id" => $tileblockAttributes["id"],
            "free_link_url" => null
        ]);

        $tileblockAttributes["tiles"] = $tiles->map(function ($tile) {
            return [
                "id" => $tile->id,
                "link_type" => "free",
                "free_link_url" => "https://code16.fr"
            ];
        });

        $this
            ->updateSharpForm("menus",
                $tileblockAttributes['id'],
                $tileblockAttributes
            )
            ->assertOk();

        foreach ($tileblockAttributes["tiles"] as $key=>$tile) {
            $this
                ->assertDatabaseHas("tiles", [
                    "id" => $tile['id'],
                    "order" => $key + 1,
                    "free_link_url" => "https://code16.fr"
                ]);
        }
    }
}