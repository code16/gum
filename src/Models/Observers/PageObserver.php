<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;

class PageObserver
{

    /**
     * @param Page $page
     */
    public function saved(Page $page)
    {
        if($page->pagegroup_id) {
            ContentUrl::createForPageInPagegroup($page);

        } elseif(!is_null($page->getOriginal()['pagegroup_id'] ?? null)) {
            // Quit pagegroup.
            $page->urls->each(function(ContentUrl $url) use($page) {
                if($url->parent
                    && $url->parent->content_type == Pagegroup::class
                    && $url->parent->content_id == $page->getOriginal()['pagegroup_id']
                ) {
                    $url->delete();
                }
            });
        }

        if(($page->getOriginal()['slug'] ?? false) && $page->getOriginal()['slug'] != $page->slug) {
            $page->urls->each(function(ContentUrl $url) {
                $url->updateUri();
            });
        }
    }

    /**
     * @param Page $page
     * @throws \Exception
     */
    public function deleted(Page $page)
    {
        $page->urls->each->delete();
    }
}