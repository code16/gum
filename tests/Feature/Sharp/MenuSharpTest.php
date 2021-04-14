<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Menus\MenuSharpForm;

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
        $this
            ->storeSharpForm("menus",
                $tileblockAttributes = factory(Tileblock::class)
                    ->make([
                        "layout" => "_menu"
                    ])
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("menus", [
                "layout" => $tileblockAttributes['layout']
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

        $this
            ->assertDatabaseHas("tileblocks", [
                "id" => $tileblockAttributes['id'],
                "layout" => "layout"
            ]);
    }
}