<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Tests\Feature\Utils\FakeSidepanelSharpForm;
use Code16\Gum\Tests\Feature\Utils\WithSharpFaker;

class SidepanelSharpTest extends GumSharpTestCase
{
    use WithSharpFaker;

    /** @test */
    function we_can_access_to_sharp_form_sidepanels()
    {
        $this->fakeSidepanelSharpForm(new class extends FakeSidepanelSharpForm {});

        $page = factory(Page::class)->create();

        $this
            ->withSharpCurrentBreadcrumb(
                [
                    ["list", "pages"],
                    ["show", "pages", $page->id]
                ]
            )
            ->getSharpForm("sidepanels")
            ->assertOk();
    }

    /** @test */
    function we_can_create_sidepanels()
    {
        $this->fakeSidepanelSharpForm(new class extends FakeSidepanelSharpForm {});

        $page = factory(Page::class)->create();

        $this
            ->withSharpCurrentBreadcrumb(
                [
                    ["list", "pages"],
                    ["show", "pages", $page->id]
                ]
            )
            ->storeSharpForm("sidepanels",
                factory(Sidepanel::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this->assertCount(1, Sidepanel::all());
    }

    /** @test */
    function we_can_update_media_in_sidepanels()
    {
        $this->fakeSidepanelSharpForm(new class extends FakeSidepanelSharpForm {});

        $sidepanel = factory(Sidepanel::class)->create([
            "layout" => "visual"
        ]);

        $this
            ->updateSharpForm("sidepanels", $sidepanel->id, [
                "downloadableFile:title" => "title",
                "downloadableFile" => "file.pdf"
            ])
            ->assertOk();

        $this
            ->assertDatabaseHas("medias", [
                "id" => 1,
                "model_type" => Sidepanel::class,
                "model_key" => "downloadableFile",
                "custom_properties" => json_encode([
                    "title" => "title"
                ])
            ]);
    }
}