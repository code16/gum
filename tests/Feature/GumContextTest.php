<?php

namespace Code16\Gum\Tests\Feature;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Models\Utils\GumContext;
use Code16\Gum\Tests\Feature\Utils\ContentUrlTestHelpers;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class GumContextTest extends TestCase
{
    use RefreshDatabase, ContentUrlTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/{path}', function() {})
            ->where('path', '[A-Za-z0-9_/-]+')
            ->middleware("build_gum_context");
    }

    /** @test */
    public function we_get_current_theme()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $section->update(["style_key" => "A"]);

        $this->get('/section/page');
        $this->assertEquals("A", GumContext::theme());

        $this->get('/section');
        $this->assertEquals("A", GumContext::theme());

        $section->update(["style_key" => "B"]);

        $this->get('/section/page');
        $this->assertEquals("B", GumContext::theme());

        $this->get('/section');
        $this->assertEquals("B", GumContext::theme());
    }

    /** @test */
    public function we_get_current_section()
    {
        list($section, $page, $tile) = $this->createSectionWithTileToPage();

        $section2 = factory(Section::class)->create(["slug" => "section2"]);
        $tile->tileblock->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $section2->id, "linkable_type" => Section::class
        ])->toArray());

        $tileblock2 = $section2->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
        factory(Page::class)->create(["slug" => "page2"]);
        $tileblock2->tiles()->create(factory(Tile::class)->make([
            "linkable_id" => $page->id, "linkable_type" => Page::class
        ])->toArray());

        $this->get('/section/page')->assertStatus(200);
        $this->assertEquals($section->id, GumContext::section()->id);

        $this->get('/section')->assertStatus(200);
        $this->assertEquals($section->id, GumContext::section()->id);

        $this->get('/section/section2/page')->assertStatus(200);
        $this->assertEquals($section2->id, GumContext::section()->id);

        $this->get('/section/section2')->assertStatus(200);
        $this->assertEquals($section2->id, GumContext::section()->id);
    }
}
