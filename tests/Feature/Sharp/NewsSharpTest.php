<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Sharp\Utils\Testing\SharpAssertions;

class NewsSharpTest extends GumSharpTestCase
{
    use SharpAssertions;

    /** @test */
    function we_can_update_news()
    {
        $this->storeSharpForm("news", [
            "title" => "Title"
        ]);

        $this->assertDatabaseHas("news", [
            "title" => "Title"
        ]);
    }
}