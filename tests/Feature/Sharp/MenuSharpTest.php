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
    function we_can_create_menus()
    {
        factory(Page::class)->create([
            "slug" => ""
        ]);

        $this
            ->storeSharpForm("menus",
                factory(Tileblock::class)
                    ->make([
                        "layout" => "_menu"
                    ])
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("tileblocks", [
                "layout" => "_menu"
            ]);
    }

    /** @test */
    function we_can_update_menus()
    {
        $tileblockAttributes = factory(Tileblock::class)->create([
            "layout" => "_menu"
        ])
            ->getAttributes();

        $tiles = factory(Tile::class, 5)->create([
            "tileblock_id" => $tileblockAttributes["id"]
        ]);

        $tileblockAttributes["tiles"] = $tiles->map(function ($tile) {
            return [
                "id" => $tile->id,
                "link_type" => "free"
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
                    "order" => $key+1
                ]);
        }
    }
}