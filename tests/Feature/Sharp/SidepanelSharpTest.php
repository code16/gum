<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SidepanelSharpTest extends TestCase
{
    use RefreshDatabase, SharpAssertions;

    protected function setUp()
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(factory(UserModel::class)->create());
    }


//    /** @test */
//    function we_can_update_a_sidepanel()
//    {
//        $panel = factory(Sidepanel::class)->create();
//
//        $values = $this->getFormValues();
//
//        $this->updateSharpForm("sidepanels", $panel->id, $values)
//            ->assertStatus(200);
//
//        $this->assertDatabaseHas("sidepanels", array_except($values, ["visual", "visual:legend"]));
//    }
//
//    /** @test */
//    function we_can_create_a_sidepanel()
//    {
//        $values = $this->getFormValues();
//
//        $this->storeSharpForm("sidepanels", $values)->assertStatus(200);
//
//        $this->assertDatabaseHas("sidepanels", array_except($values, ["visual", "visual:legend"]));
//    }
//
//    /** @test */
//    function slug_is_generated_if_missing()
//    {
//        $values = $this->getFormValues([
//            "title" => "my long title",
//            "slug" => ""
//        ]);
//
//        $this->storeSharpForm("sidepanels", $values)->assertStatus(200);
//
//        $this->assertEquals("my-long-title", Sidepanel::first()->slug);
//    }
//
//    /** @test */
//    function validation_works()
//    {
//        $this->storeSharpForm("sidepanels", $this->getFormValues([
//            "slug" => "a wrong slug"
//        ]))->assertStatus(422);
//
//        $this->storeSharpForm("sidepanels", $this->getFormValues([
//            "title" => ""
//        ]))->assertStatus(422);
//
//        $this->storeSharpForm("sidepanels", $this->getFormValues([
//            "body_text" => ["text" => ""]
//        ]))->assertStatus(422);
//    }
//
//    /**
//     * @param array $formValues
//     * @return array
//     */
//    private function getFormValues(array $formValues = [])
//    {
//        return array_merge([
//            "title" => "title",
//            "short_title" => "short",
//            "body_text" => ["text" => "body"],
//            "slug" => "slug",
//            "visual" => "test.jpg",
//        ], $formValues);
//    }

}