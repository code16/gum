<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;

class SectionSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_update_a_section()
    {
        $section = factory(Section::class)->create();

        $this->updateSharpForm("sections", $section->id, $this->getFormValues())
            ->assertStatus(200);

        $this->assertDatabaseHas("sections", $this->getFormValues() + ["is_root" => false]);
    }

    /** @test */
    function we_can_create_a_section()
    {
        $this->storeSharpForm("sections", $this->getFormValues())->assertStatus(200);

        $this->assertDatabaseHas("sections", $this->getFormValues() + ["is_root" => false]);
    }

    /** @test */
    function slug_is_generated_if_missing()
    {
        $values = $this->getFormValues([
            "title" => "my long title",
            "slug" => ""
        ]);

        $this->storeSharpForm("sections", $values)->assertStatus(200);

        $this->assertEquals("my-long-title", Section::first()->slug);
    }

    /** @test */
    function validation_works()
    {
        $this->storeSharpForm("sections", $this->getFormValues([
            "slug" => "a wrong slug"
        ]))->assertStatus(422);

        $this->storeSharpForm("sections", $this->getFormValues([
            "title" => ""
        ]))->assertStatus(422);

        app()['config']->set([
            "gum" => [
                "styles" => ["style" => "Style"]
            ]
        ]);

        $this->storeSharpForm("sections", $this->getFormValues([
            "style_key" => ""
        ]))->assertStatus(422);

        $this->storeSharpForm("sections", $this->getFormValues([
            "style_key" => "pp"
        ]))->assertStatus(422);
    }

    /** @test */
    function domain_is_set_to_null_if_not_configured()
    {
        config()->set(["gum.domains" => null]);

        $this->storeSharpForm("sections", $this->getFormValues())->assertStatus(200);

        $this->assertNull(Section::first()->domain);
    }

    /** @test */
    function domain_is_set_to_default_value_if_configured()
    {
        config()->set(["gum.domains" => [
            "a" => "A", "b" => "B"
        ]]);

        $this->storeSharpForm("sections", $this->getFormValues())->assertStatus(200);

        $this->assertEquals("a", Section::first()->domain);
    }

    /** @test */
    function domain_is_set_to_current_session_value()
    {
        config()->set(["gum.domains" => [
            "a" => "A", "b" => "B"
        ]]);

        SharpGumSessionValue::setDomain("b");

        $this->storeSharpForm("sections", $this->getFormValues())->assertStatus(200);

        $this->assertEquals("b", Section::first()->domain);
    }

    /** @test */
    function domain_is_set_to_first_if_current_session_value_is_wrong()
    {
        config()->set(["gum.domains" => [
            "a" => "A", "b" => "B"
        ]]);

        SharpGumSessionValue::setDomain("c");

        $this->storeSharpForm("sections", $this->getFormValues())->assertStatus(200);

        $this->assertEquals("a", Section::first()->domain);
    }

    /**
     * @param array $formValues
     * @return array
     */
    private function getFormValues(array $formValues = [])
    {
        return array_merge([
            "title" => "title",
            "short_title" => "short",
            "heading_text" => ["text" => "heading"],
            "slug" => "slug",
        ], $formValues);
    }

}