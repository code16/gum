<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Page;

class PageSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_access_to_sharp_form_pages()
    {
        $this
            ->getSharpForm("pages")
            ->assertOk();
    }

    /** @test */
    function we_can_create_pages()
    {
        $this
            ->storeSharpForm("pages",
                $pageAttributes = factory(Page::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("pages", [
                "title" => $pageAttributes['title']
            ]);
    }

    /** @test */
    function we_can_update_pages()
    {
        $pageAttributes = factory(Page::class)->create([
            "title" => "Title"
        ])
            ->getAttributes();

        $pageAttributes["title"] = "Updated";

        $this
            ->updateSharpForm("pages",
                $pageAttributes['id'],
                $pageAttributes
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("pages", [
                "id" => $pageAttributes['id'],
                "title" => "Updated"
            ]);
    }
}