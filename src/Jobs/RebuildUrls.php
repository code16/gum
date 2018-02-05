<?php

namespace Code16\Gum\Jobs;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RebuildUrls
{
    use Dispatchable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->clearUrls();

        Section::where("is_root", true)
            ->each(function(Section $section) {
                // Handle sections witch aren't linked with a Tile (root sections)
                $section->url()->create([
                    "uri" => (new ContentUrl())->findAvailableUriFor($section, $section->domain),
                    "domain" => $section->domain,
                    "visibility" => "ONLINE",
                ]);

                $this->createUrlsForSection($section);
            });
    }

    /**
     * @param Section $section
     */
    protected function createUrlsForSection(Section $section)
    {
        $section->tileblocks->each(function(Tileblock $tileblock) use($section) {
            $tileblock->tiles->each(function(Tile $tile) use($section) {
                ContentUrl::createForTile($section, $tile);

                if($tile->linkable_type == Section::class) {
                    $this->createUrlsForSection($tile->linkable);
                }
            });
        });
    }

    protected function clearUrls()
    {
        ContentUrl::all()->each->delete();
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ContentUrl::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
