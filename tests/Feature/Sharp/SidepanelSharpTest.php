<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Sidepanels\SidepanelSharpForm;

class SidepanelSharpTest extends GumSharpTestCase
{

    /** @test */
    function we_can_access_to_sharp_form_sidepanels()
    {
        $this
            ->getSharpForm("sidepanels")
            ->assertOk();
    }

    /** @test */
    function we_can_create_sidepanels()
    {
        $this
            ->getMockForAbstractClass(SidepanelSharpForm::class);

        $this
            ->storeSharpForm("sidepanels",
                $sidepanelAttributes = factory(Sidepanel::class)
                    ->make()
                    ->getAttributes()
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("sidepanels", [
                "title" => $sidepanelAttributes['title']
            ]);
    }

    /** @test */
    function we_can_update_sidepanels()
    {
        $this
            ->getMockForAbstractClass(SidepanelSharpForm::class);

        $sidepanelAttributes = factory(Sidepanel::class)->create([
            "title" => "Title"
        ])
            ->getAttributes();

        $sidepanelAttributes["title"] = "Updated";

        $this
            ->updateSharpForm("sidepanels",
                $sidepanelAttributes['id'],
                $sidepanelAttributes
            )
            ->assertOk();

        $this
            ->assertDatabaseHas("sidepanels", [
                "id" => $sidepanelAttributes['id'],
                "title" => "Updated"
            ]);
    }
}