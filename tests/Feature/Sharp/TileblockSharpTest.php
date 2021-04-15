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
    function we_can_update_tiles_in_tileblock()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblockAttributes = factory(Tileblock::class)->create()
            ->getAttributes();

        $tiles = factory(Tile::class, 5)->create([
            "tileblock_id" => $tileblockAttributes["id"]
        ]);

        $tileblockAttributes["tiles"] = $tiles->map(function ($tile) {
            return [
                "id" => $tile->id,
                "link_type" => "free",
                "free_link_url" => "https://code16.fr"
            ];
        });

        $this
            ->updateSharpForm("tileblocks",
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

    /** @test */
    function we_cant_update_tiles_in_tileblock_without_free_link()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblockAttributes = factory(Tileblock::class)->create()
            ->getAttributes();

        $tiles = factory(Tile::class, 2)->create([
            "tileblock_id" => $tileblockAttributes["id"],
            "free_link_url" => "https://code16.fr"
        ]);

        $tileblockAttributes["tiles"] = $tiles->map(function ($tile) {
            return [
                "id" => $tile->id,
                "link_type" => "free",
                "free_link_url" => null
            ];
        });

        $this
            ->updateSharpForm("tileblocks",
                $tileblockAttributes['id'],
                $tileblockAttributes
            )
            ->assertStatus(422);

        foreach ($tileblockAttributes["tiles"] as $tile) {
            $this
                ->assertDatabaseMissing("tiles", [
                    "id" => $tile['id'],
                    "free_link_url" => null
                ]);
        }
    }

    /** @test */
    function we_cant_update_tiles_in_tileblock_without_associated_page_link()
    {
        $this->fakeTileblockSharpForm(new class extends FakeTileblockSharpForm {});

        $tileblockAttributes = factory(Tileblock::class)->create()
            ->getAttributes();

        $tiles = factory(Tile::class, 2)->create([
            "tileblock_id" => $tileblockAttributes["id"],
            "page_id" => factory(Page::class)->create()->id
        ]);

        $tileblockAttributes["tiles"] = $tiles->map(function ($tile) {
            return [
                "id" => $tile->id,
                "link_type" => "page",
                "page_id" => null
            ];
        });

        $this
            ->updateSharpForm("tileblocks",
                $tileblockAttributes['id'],
                $tileblockAttributes
            )
            ->assertStatus(422);

        foreach ($tileblockAttributes["tiles"] as $tile) {
            $this
                ->assertDatabaseMissing("tiles", [
                    "id" => $tile['id'],
                    "page_id" => null
                ]);
        }
    }
}