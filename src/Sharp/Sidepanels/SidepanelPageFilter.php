<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Page;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\EntityListRequiredFilter;

class SidepanelPageFilter implements EntityListRequiredFilter
{

    public function label()
    {
        return "Page";
    }

    /**
     * @return array
     */
    public function values()
    {
        $pages = Page::orderBy("title");

        $this->queryDomain($pages);

        return $pages->with("urls")->get()
            ->pluck("title", "id")
            ->all();
    }

    /**
     * @return string|int
     */
    public function defaultValue()
    {
        if($pageId = SharpGumSessionValue::get("sidepanel_page")) {
            $page = Page::where("id", $pageId);
            $this->queryDomain($page);
            if($page->count()) {
                return $pageId;
            }
        }

        $page = Page::orderBy("title");
        $this->queryDomain($page);

        return $page->first()->id ?? null;
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function searchKeys(): array
    {
        return ["title", "url"];
    }

    public function template()
    {
        return "{{title}}<br><small>{{uri}}</small>";
    }

    private function queryDomain(&$pages)
    {
        if(sizeof(config("gum.domains"))) {
            $pages->where(function ($query) {
                $query->whereExists(function ($query) {
                    return $query->from("content_urls")
                        ->whereRaw("content_id = pages.id")
                        ->where("content_type", Page::class)
                        ->where("domain", SharpGumSessionValue::getDomain());
                })->orWhereNotExists(function ($query) {
                    return $query->from("content_urls")
                        ->whereRaw("content_id = pages.id")
                        ->where("content_type", Page::class);
                });
            });
        }
    }
}