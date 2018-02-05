<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Pagegroup;

class PagegroupObserver
{
    /**
     * @param Pagegroup $pagegroup
     */
    public function updated(Pagegroup $pagegroup)
    {
        if($pagegroup->getOriginal()['slug'] != $pagegroup->slug) {
            $pagegroup->urls->each(function(ContentUrl $url) {
                $url->updateUri();
            });
        }
    }

    /**
     * @param Pagegroup $pagegroup
     */
    public function deleted(Pagegroup $pagegroup)
    {
        $pagegroup->urls->each->delete();
    }
}