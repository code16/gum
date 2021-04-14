<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Tiles\TileblockSharpForm;

class TileblockSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_access_to_sharp_form_tileblocks()
    {
        $this
            ->getSharpForm("tileblocks")
            ->assertOk();
    }

    /** @test */
    function we_can_create_tileblocks()
    {
        $this->getMockForAbstractClass(TileblockSharpForm::class);

        $this
            ->storeSharpForm("tileblocks",
                $tileblockAttributes = factory(Tileblock::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("tileblocks", [
                "layout" => $tileblockAttributes['layout']
            ]);
    }

    /** @test */
    function we_can_update_tileblocks()
    {
        $this
            ->getMockForAbstractClass(TileblockSharpForm::class);

        $tileblockAttributes = factory(Tileblock::class)->create([
            "layout" => "checkerboard"
        ])
            ->getAttributes();

        $tileblockAttributes["layout"] = "layout";

        $this
            ->updateSharpForm("tileblocks",
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