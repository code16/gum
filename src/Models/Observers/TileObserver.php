<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;

class TileObserver
{

    /**
     * @param Tile $tile
     */
    public function saved(Tile $tile)
    {
        $mustRemove = $tile->mustRemoveOldUrl;
        $mustCreate = $tile->mustCreateNewUrl;

        $tile->mustRemoveOldUrl = false;
        $tile->mustCreateNewUrl = false;

        $tile->refresh();

        if($mustRemove && $tile->contentUrl) {
            if($this->tileUrlIsUsedElsewhere($tile)) {
                $tile->contentUrl()->dissociate()->save();
            } else {
                $tile->contentUrl->delete();
            }
        }

        if($mustCreate) {
            ContentUrl::createForTile($tile->tileblock->section, $tile);
        }

        $this->updateUrlVisibility($tile);
    }

    /**
     * @param Tile $tile
     */
    public function deleted(Tile $tile)
    {
        if($tile->contentUrl && !$this->tileUrlIsUsedElsewhere($tile)) {
            // There is no other Tile linked to this content, remove URL
            $tile->contentUrl->delete();
        }
    }

    /**
     * @param Tile $tile
     */
    protected function updateUrlVisibility(Tile $tile)
    {
        if(is_null($url = $tile->fresh()->contentUrl)) {
            return;
        }

        if($url->content_type == Section::class) {
            // Visibility IS NOT cascade down for Sections,
            // since they have only ONE url.
            return;
        }

        $url->update([
            "visibility" => $tile->visibility,
            "published_at" => $tile->published_at,
            "unpublished_at" => $tile->unpublished_at,
        ]);
    }

    protected function tileUrlIsUsedElsewhere(Tile $tile): bool
    {
        return $tile->contentUrl
            && Tile::where("content_url_id", $tile->content_url_id)
                ->where("id", "!=", $tile->id)
                ->count() != 0;
    }
}