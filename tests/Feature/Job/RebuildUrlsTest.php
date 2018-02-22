<?php

namespace Code16\Gum\Tests\Feature\Job;

use Code16\Gum\Jobs\RebuildUrls;
use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RebuildUrlsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_rebuilds_urls_like_before()
    {
        $this->buildUrls();

        $urls = ContentUrl::all()
            ->map(function(ContentUrl $url) {
                return collect($url->toArray())
                    ->only([
                        "uri", "content_id", "content_type",
                        "visibility", "published_at", "unpublished_at"
                    ])
                    ->all();
            });

        RebuildUrls::dispatch();

        $newUrls = ContentUrl::all();

        $this->assertCount($urls->count(), $newUrls);

        $newUrls->each(function(ContentUrl $url) use($urls) {
            $previousUrl = $urls->where("uri", $url->uri)->first();

            $this->assertEquals(
                $previousUrl,
                collect($url->toArray())
                    ->only([
                        "uri", "content_id", "content_type",
                        "visibility", "published_at", "unpublished_at"
                    ])
                    ->all()
            );
        });
    }

    /** @test */
    function if_a_slug_was_updated_it_is_set_back_by_rebuildUrls()
    {
        $this->buildUrls();

        $page = ContentUrl::where("content_type", Page::class)
            ->inRandomOrder()
            ->first()
            ->content;

        $page->update([
            "slug"=>"some-new-slug"
        ]);

        RebuildUrls::dispatch();

        $this->assertStringEndsWith(
            "some-new-slug",
            ContentUrl::where("content_id", $page->id)
                ->where("content_type", Page::class)
                ->first()
                ->uri
        );
    }

    private function buildUrls()
    {
        $sections = [];

        $homeSection = factory(Section::class)->create([
            "slug" => "",
            "is_root" => true,
        ]);

        $homeSection->url()->create([
            "uri" => "/",
            "visibility" => "ONLINE"
        ]);

        for($k=1; $k<=5; $k++) {
            $sections[] = factory(Section::class)->create([
                "is_root" => true,
            ]);
        }

        for($k=1; $k<=10; $k++) {
            $subsection = factory(Section::class)->create([
                "is_root" => false
            ]);

            $tileblock = $sections[array_rand($sections)]->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
            $tileblock->tiles()->create(factory(Tile::class)->make([
                "linkable_id" => $subsection->id,
                "linkable_type" => Section::class,
                "visibility" => (rand(1, 9) < 4 ? 'OFFLINE' : 'ONLINE')
            ])->toArray());

            $sections[] = $subsection;
        }

        foreach($sections as $k => $section) {
            for($ki=0; $ki < rand(1, 3); $ki++) {
                $pages = factory(Page::class, 2)->create();
                $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());

                foreach ($pages as $i => $page) {
                    if ($i <= $k) {
                        $tileblock->tiles()->create(factory(Tile::class)->make([
                            "linkable_id" => $page->id,
                            "linkable_type" => Page::class,
                            "visibility" => (rand(1, 9) < 4 ? 'OFFLINE' : 'ONLINE')
                        ])->toArray());
                    }
                }
            }

            $pagegroup = factory(Pagegroup::class)->create();
            factory(Page::class, 2)->create(["pagegroup_id" => $pagegroup->id]);
            $tileblock = $section->tileblocks()->create(factory(Tileblock::class)->make()->toArray());
            $tileblock->tiles()->create(factory(Tile::class)->make([
                "linkable_id" => $pagegroup->id,
                "linkable_type" => Pagegroup::class,
                "visibility" => (rand(1, 9) < 4 ? 'OFFLINE' : 'ONLINE')
            ])->toArray());
        }
    }
}