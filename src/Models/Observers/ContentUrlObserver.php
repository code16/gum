<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Pagegroup;

class ContentUrlObserver
{
    /**
     * @param ContentUrl $contentUrl
     */
    public function created(ContentUrl $contentUrl)
    {
        if($contentUrl->content_type == Pagegroup::class) {
            // Create Urls for Pages in this Pagegroup.
            $contentUrl->content->pages()->each(function($page) use($contentUrl) {
                ContentUrl::findOrCreateSubContentUrl($page, $contentUrl);
            });
        }
    }

    /**
     * @param ContentUrl $contentUrl
     */
    public function updated(ContentUrl $contentUrl)
    {
        if($contentUrl->content_type == Pagegroup::class) {
            // Cascade down visibility
            $contentUrl->children->each(function (ContentUrl $url) use ($contentUrl) {
                $url->update([
                    "visibility" => $contentUrl->visibility,
                    "published_at" => $contentUrl->published_at,
                    "unpublished_at" => $contentUrl->unpublished_at,
                ]);
            });
        }
    }

    /**
     * @param ContentUrl $contentUrl
     */
    public function deleted(ContentUrl $contentUrl)
    {
    }
}