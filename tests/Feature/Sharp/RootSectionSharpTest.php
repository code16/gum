<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Section;

class RootSectionSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_update_a_root_section()
    {
        $rootSection = factory(Section::class)->create(["is_root" => true]);

        $values = [
            "title" => "title",
            "short_title" => "short",
            "heading_text" => ["text" => "heading"],
            "style_key" => "style",
            "slug" => "slug",
        ];

        $this->updateSharpForm("root_sections", $rootSection->id, $values)->assertStatus(200);

        $this->assertDatabaseHas("sections", $values + ["is_root" => true]);
    }

    /** @test */
    function we_can_create_a_root_section()
    {
        $values = [
            "title" => "title",
            "short_title" => "short",
            "heading_text" => ["text" => "heading"],
            "style_key" => "style",
            "slug" => "slug",
        ];

        $this->storeSharpForm("root_sections", $values)->assertStatus(200);

        $this->assertDatabaseHas("sections", $values + ["is_root" => true]);
    }

    /** @test */
    function new_root_sections_have_their_uri_set()
    {
        $values = [
            "title" => "title",
            "short_title" => "short",
            "heading_text" => ["text" => "heading"],
            "style_key" => "style",
            "slug" => "slug",
        ];

        $this->storeSharpForm("root_sections", $values)->assertStatus(200);

        $this->assertEquals("/slug", Section::first()->url->uri);
        $this->assertEquals("OFFLINE", Section::first()->url->visibility);
    }

}