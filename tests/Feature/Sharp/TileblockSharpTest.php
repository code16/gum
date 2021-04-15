<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\Feature\Utils\FakeTileblockSharpForm;
use Code16\Gum\Tests\Feature\Utils\WithSharpFaker;

class TileblockSharpTest extends GumSharpTestCase
{
    use WithSharpFaker;

    /** @test */
    function we_can_access_to_sharp_form_tileblocks()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $page = factory(Page::class)->create();

        $this
            ->withSharpCurrentBreadcrumb(
                [
                    ["list", "pages"],
                    ["show", "pages", $page->id]
                ]
            )
            ->getSharpForm("tileblocks")
            ->assertOk();
    }

    /** @test */
    function we_can_create_tileblocks()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $page = factory(Page::class)->create();

        $this
            ->withSharpCurrentBreadcrumb(
                [
                    ["list", "pages"],
                    ["show", "pages", $page->id]
                ]
            )
            ->storeSharpForm("tileblocks",
                factory(Tileblock::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this->assertCount(1, Tileblock::all());
    }

    /** @test */
    function we_can_update_tiles_in_tileblocks()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblock = factory(Tileblock::class)->create();

        $tiles = factory(Tile::class, 5)->create([
            "tileblock_id" => $tileblock->id,
            "free_link_url" => null
        ]);

        $this
            ->updateSharpForm("tileblocks",
                $tileblock->id,
                collect($tileblock->getAttributes())
                    ->merge([
                        "tiles" => $tiles
                            ->map(function (Tile $tile) {
                                return [
                                    "id" => $tile->id,
                                    "link_type" => "free",
                                    "free_link_url" => "https://code16.fr/" . $tile->id
                                ];
                            })
                            ->reverse()
                            ->values()
                    ])
                    ->toArray()
            )
            ->assertOk();
        
        $tiles->each(function(Tile $tile, $order) use($tiles) {
            $this->assertDatabaseHas("tiles", [
                "id" => $tile->id,
                "order" => count($tiles) - $order, // Reversed order
                "free_link_url" => "https://code16.fr/" . $tile->id
            ]);
        });
    }

    /** @test */
    function we_cant_update_tiles_in_tileblock_without_free_link()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblock = factory(Tileblock::class)->create();

        $tile = factory(Tile::class)->create([
            "tileblock_id" => $tileblock->id
        ]);

        $this
            ->updateSharpForm("tileblocks",
                $tileblock->id,
                collect($tileblock->getAttributes())
                    ->merge([
                        "tiles" => [
                            [
                                "id" => $tile->id,
                                "link_type" => "free",
                                "free_link_url" => null
                            ]
                        ]
                    ])
                    ->toArray()
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors("tiles.0.free_link_url");
    }

    /** @test */
    function we_cant_update_tiles_in_tileblock_without_associated_page_link()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblock = factory(Tileblock::class)->create();
        
        $tile = factory(Tile::class)->create([
            "tileblock_id" => $tileblock->id,
            "page_id" => factory(Page::class)->create()->id
        ]);

        $this
            ->updateSharpForm("tileblocks",
                $tileblock->id,
                collect($tileblock->getAttributes())
                    ->merge([
                        "tiles" => [
                            [
                                "id" => $tile->id,
                                "link_type" => "page",
                                "page_id" => null
                            ]
                        ]
                    ])
                    ->toArray()
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors("tiles.0.page_id");
    }
}