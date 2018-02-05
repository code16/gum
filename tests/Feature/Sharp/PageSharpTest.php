<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PageSharpTest extends TestCase
{
    use RefreshDatabase, SharpAssertions;

    protected function setUp()
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(factory(UserModel::class)->create());
    }


    /** @test */
    function we_can_update_a_page()
    {
        $page = factory(Page::class)->create();

        $values = $this->getFormValues();

        $this->updateSharpForm("pages", $page->id, $values)
            ->assertStatus(200);

        $this->assertDatabaseHas("pages", array_except($values, ["visual", "visual:legend"]));
    }

    /** @test */
    function we_can_create_a_page()
    {
        $values = $this->getFormValues();

        $this->storeSharpForm("pages", $values)->assertStatus(200);

        $this->assertDatabaseHas("pages", array_except($values, ["visual", "visual:legend"]));
    }

    /** @test */
    function slug_is_generated_if_missing()
    {
        $values = $this->getFormValues([
            "title" => "my long title",
            "slug" => ""
        ]);

        $this->storeSharpForm("pages", $values)->assertStatus(200);

        $this->assertEquals("my-long-title", Page::first()->slug);
    }

    /** @test */
    function an_url_is_created_if_page_belongs_to_a_referenced_pagegroup()
    {
        $pagegroup = factory(Pagegroup::class)->create(["slug" => "pagegroup"]);
        $section = factory(Section::class)->create(["slug" => "section"]);
        $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        $tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $pagegroup->id, "linkable_type" => Pagegroup::class
        ])->toArray());

        $values = $this->getFormValues(["pagegroup_id" => $pagegroup->id]);

        $this->storeSharpForm("pages", $values)->assertStatus(200);

        $page = Page::first();

        $this->assertEquals($pagegroup->id, $page->pagegroup_id);
        $this->assertEquals("/section/pagegroup/slug", $page->urls[0]->uri);
    }

    /** @test */
    function validation_works()
    {
        $this->storeSharpForm("pages", $this->getFormValues([
            "slug" => "a wrong slug"
        ]))->assertStatus(422);

        $this->storeSharpForm("pages", $this->getFormValues([
            "title" => ""
        ]))->assertStatus(422);

        $this->storeSharpForm("pages", $this->getFormValues([
            "body_text" => ["text" => ""]
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
            "body_text" => ["text" => "body"],
            "slug" => "slug",
            "visual" => "test.jpg",
        ], $formValues);
    }

}