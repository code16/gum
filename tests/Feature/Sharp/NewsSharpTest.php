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
        $news = factory(News::class)->create();

        $this
            ->updateSharpForm(
                "news", 
                $news->id,
                collect($news->getAttributes())->merge(['title' => 'Updated'])->toArray()
            )
            ->assertOk();

        $this->assertDatabaseHas("news", [
            "id" => $news->id,
            "title" => "Updated"
        ]);
    }
}