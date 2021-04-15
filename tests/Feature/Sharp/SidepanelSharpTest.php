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

        $sidepanel = factory(Sidepanel::class)->create([
            "page_id" => factory(Page::class)->create()->id
        ]);

        $this
            ->getSharpForm("sidepanels", $sidepanel->id)
            ->assertOk();
    }

    /** @test */
    function we_can_create_sidepanels()
    {
        $this->fakeSidepanelSharpForm(new class extends FakeSidepanelSharpForm {});

        $this
            ->withSharpCurrentBreadcrumb([
                ["show", "pages"]
            ])
            ->storeSharpForm("sidepanels",
                factory(Sidepanel::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertCount(1, Sidepanel::all());
    }

    /** @test */
    function we_can_update_sidepanels()
    {
        $this->fakeSidepanelSharpForm(new class extends FakeSidepanelSharpForm {});

        $sidepanelAttributes = factory(Sidepanel::class)->create([
            "layout" => "visual"
        ])
            ->getAttributes();

        $sidepanelAttributes['layout'] = "breadcrumb";

        $this
            ->updateSharpForm("sidepanels",
                $sidepanelAttributes['id'],
                $sidepanelAttributes
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("sidepanels", [
                "id" => $sidepanelAttributes['id'],
                "layout" => "breadcrumb"
            ]);
    }
}