<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\News;

class NewsSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_update_news()
    {
        $this->storeSharpForm("news", [
            $news = factory(News::class)
                ->make()
                ->getAttributes()
        ]);

        $this->assertDatabaseHas("news", [
            "title" => $news['title']
        ]);
    }
}