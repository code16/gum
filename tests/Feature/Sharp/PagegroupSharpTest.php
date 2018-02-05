<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PagegroupSharpTest extends TestCase
{
    use RefreshDatabase, SharpAssertions;

    protected function setUp()
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(factory(UserModel::class)->create());
    }


    /** @test */
    function we_can_update_a_pagegroup()
    {
        $pagegroup = factory(Pagegroup::class)->create();

        $this->updateSharpForm("pagegroups", $pagegroup->id, $this->getFormValues())
            ->assertStatus(200);

        $this->assertDatabaseHas("pagegroups", $this->getFormValues());
    }

    /** @test */
    function we_can_create_a_pagegroup()
    {
        $this->storeSharpForm("pagegroups", $this->getFormValues())->assertStatus(200);

        $this->assertDatabaseHas("pagegroups", $this->getFormValues());
    }

    /** @test */
    function slug_is_generated_if_missing()
    {
        $values = $this->getFormValues([
            "title" => "my long title",
            "slug" => ""
        ]);

        $this->storeSharpForm("pagegroups", $values)->assertStatus(200);

        $this->assertEquals("my-long-title", Pagegroup::first()->slug);
    }

    /** @test */
    function validation_works()
    {
        $this->storeSharpForm("pagegroups", $this->getFormValues([
            "slug" => "a wrong slug"
        ]))->assertStatus(422);

        $this->storeSharpForm("pagegroups", $this->getFormValues([
            "title" => ""
        ]))->assertStatus(422);
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
            "slug" => "slug",
        ], $formValues);
    }

}