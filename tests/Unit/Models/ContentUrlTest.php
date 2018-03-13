<?php

namespace Code16\Gum\Tests\Unit\Models;

use Carbon\Carbon;
use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentUrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function is_visible_works_recursively()
    {
        $sectionUrl = ContentUrl::create([
            "uri" => "/section", "content_id" => factory(Section::class)->create()->id, "content_type" => Section::class,
            "visibility" => "ONLINE"
        ]);

        $pagegroupUrl = ContentUrl::create([
            "uri" => "/section/pagegroup", "content_id" => factory(Pagegroup::class)->create()->id, "content_type" => Pagegroup::class,
            "visibility" => "ONLINE",
            "parent_id" => $sectionUrl->id
        ]);

        $pageUrl = ContentUrl::create([
            "uri" => "/section/pagegroup/page", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "visibility" => "ONLINE",
            "parent_id" => $pagegroupUrl->id
        ]);

        $page2Url = ContentUrl::create([
            "uri" => "/section/pagegroup/page2", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "visibility" => "OFFLINE",
            "parent_id" => $pagegroupUrl->id
        ]);

        $this->assertTrue($sectionUrl->isVisible());
        $this->assertTrue($pagegroupUrl->isVisible());
        $this->assertTrue($pageUrl->isVisible());
        $this->assertFalse($page2Url->isVisible());

        $pagegroup2Url = ContentUrl::create([
            "uri" => "/section/pagegroup2", "content_id" => factory(Pagegroup::class)->create()->id, "content_type" => Pagegroup::class,
            "visibility" => "OFFLINE",
            "parent_id" => $sectionUrl->id
        ]);

        $page3Url = ContentUrl::create([
            "uri" => "/section/pagegroup2/page", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "visibility" => "ONLINE",
            "parent_id" => $pagegroup2Url->id
        ]);

        $this->assertFalse($pagegroup2Url->isVisible());
        $this->assertFalse($page3Url->isVisible());
    }

    /** @test */
    function is_published_works_recursively()
    {
        $sectionUrl = ContentUrl::create([
            "uri" => "/section", "content_id" => factory(Section::class)->create()->id, "content_type" => Section::class
        ]);

        $pagegroupUrl = ContentUrl::create([
            "uri" => "/section/pagegroup", "content_id" => factory(Pagegroup::class)->create()->id, "content_type" => Pagegroup::class,
            "published_at" => Carbon::yesterday(), "unpublished_at" => Carbon::tomorrow(),
            "parent_id" => $sectionUrl->id
        ]);

        $pageUrl = ContentUrl::create([
            "uri" => "/section/pagegroup/page", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "published_at" => Carbon::yesterday(), "unpublished_at" => Carbon::tomorrow(),
            "parent_id" => $pagegroupUrl->id
        ]);

        $page2Url = ContentUrl::create([
            "uri" => "/section/pagegroup/page2", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "published_at" => Carbon::yesterday()->subDay(), "unpublished_at" => Carbon::yesterday(),
            "parent_id" => $pagegroupUrl->id
        ]);

        $this->assertTrue($sectionUrl->isPublished());
        $this->assertTrue($pagegroupUrl->isPublished());
        $this->assertTrue($pageUrl->isPublished());
        $this->assertFalse($page2Url->isPublished());

        $pagegroup2Url = ContentUrl::create([
            "uri" => "/section/pagegroup2", "content_id" => factory(Pagegroup::class)->create()->id, "content_type" => Pagegroup::class,
            "published_at" => Carbon::tomorrow(),
            "parent_id" => $sectionUrl->id
        ]);

        $page3Url = ContentUrl::create([
            "uri" => "/section/pagegroup2/page", "content_id" => factory(Page::class)->create()->id, "content_type" => Page::class,
            "published_at" => Carbon::yesterday(),
            "parent_id" => $pagegroup2Url->id
        ]);

        $this->assertFalse($pagegroup2Url->isPublished());
        $this->assertFalse($page3Url->isPublished());
    }

    /** @test */
    function we_can_update_an_uri()
    {
        list($sectionUrl, $pagegroupUrl, $pageUrl) = $this->buildHierarchy();

        $pagegroupUrl->updateUri(null, "new-pagegroup");

        $this->assertEquals("/section/new-pagegroup", $pagegroupUrl->fresh()->uri);
        $this->assertEquals("/section/new-pagegroup/page", $pageUrl->fresh()->uri);

        $sectionUrl->updateUri(null, "new-section");

        // NB: pagegroup's uri is back to "/pagegroup" because it is based on
        // actual linked Pagegroup slug, which wasn't updated
        $this->assertEquals("/new-section", $sectionUrl->uri);
        $this->assertEquals("/new-section/pagegroup", $pagegroupUrl->fresh()->uri);
        $this->assertEquals("/new-section/pagegroup/page", $pageUrl->fresh()->uri);
    }

    /** @test */
    function we_can_build_a_breadcrumb()
    {
        list($sectionUrl, $pagegroupUrl, $pageUrl) = $this->buildHierarchy();

        $this->assertEquals([
            "/section" => $sectionUrl->content->menu_title,
        ], $sectionUrl->buildBreadcrumb());

        $this->assertEquals([
            "/section" => $sectionUrl->content->menu_title,
            "/section/pagegroup" => $pagegroupUrl->content->menu_title,
        ], $pagegroupUrl->buildBreadcrumb());

        $this->assertEquals([
            "/section" => $sectionUrl->content->menu_title,
            "/section/pagegroup" => $pagegroupUrl->content->menu_title,
            "/section/pagegroup/page" => $pageUrl->content->menu_title,
        ], $pageUrl->buildBreadcrumb());
    }

    /**
     * @return array
     */
    private function buildHierarchy()
    {
        $section = factory(Section::class)->create(["slug"=>"section"]);
        $pagegroup = factory(Pagegroup::class)->create(["slug"=>"pagegroup"]);
        $page = factory(Page::class)->create(["slug"=>"page"]);

        $sectionUrl = ContentUrl::create([
            "uri" => "/section", "content_id" => $section->id, "content_type" => Section::class,
            "visibility" => "ONLINE"
        ]);

        $pagegroupUrl = ContentUrl::create([
            "uri" => "/section/pagegroup", "content_id" => $pagegroup->id, "content_type" => Pagegroup::class,
            "visibility" => "ONLINE",
            "parent_id" => $sectionUrl->id
        ]);

        $pageUrl = ContentUrl::create([
            "uri" => "/section/pagegroup/page", "content_id" => $page->id, "content_type" => Page::class,
            "visibility" => "ONLINE",
            "parent_id" => $pagegroupUrl->id
        ]);

        return [$sectionUrl, $pagegroupUrl, $pageUrl];
    }
}