<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\News;

class NewsSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_access_to_sharp_form_news()
    {
        $this
            ->getSharpForm("news")
            ->assertOk();
    }

    /** @test */
    function we_can_create_news()
    {
        $this
            ->storeSharpForm("news",
                $newsAttributes = factory(News::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("news", [
                "title" => $newsAttributes['title']
            ]);
    }

    /** @test */
    function we_can_update_news()
    {
        $newsAttributes = factory(News::class)->create([
            "title" => "Title"
        ])
            ->getAttributes();

        $newsAttributes["title"] = "Updated";

        $this
            ->updateSharpForm("news",
                $newsAttributes['id'],
                $newsAttributes
        )
            ->assertOk();

        $this
            ->assertDatabaseHas("news", [
                "id" => $newsAttributes['id'],
                "title" => "Updated"
        ]);
    }
}