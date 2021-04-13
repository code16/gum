<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Carbon\Carbon;
use Code16\Gum\Models\News;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;

class NewsSharpTest extends GumSharpTestCase
{
    use SharpAssertions;

    /** @test */
    function we_can_create_news()
    {
        Carbon::setTestNow();

        $news = factory(News::class)->make();

        $this
            ->getSharpForm("news", $news->id)
            ->assertOk();

        $this
            ->storeSharpForm("news", [
                "title" => "Titre"
            ])
            ->assertOk();

        $this->assertDatabaseHas("news", [
            "title" => "Titre"
        ]);
    }
}