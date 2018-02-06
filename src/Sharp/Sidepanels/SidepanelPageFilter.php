<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\ContentUrl;
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
            ->map(function(Page $page) {
                return [
                    "id" => $page->id,
                    "label" => $page->title,
                    "uri" => $page->urls->map(function(ContentUrl $url) {
                        $uri = $url->uri;
                        if(strlen($uri) > 60) {
                            $uri = substr($url->uri, 0, 25)
                                . ' [...] '
                                . substr($url->uri, -25);
                        }
                        return implode(" <strong>/</strong> ", explode("/", $uri));
                    })->implode('<br>')
                ];
            })
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
        return ["label", "uri"];
    }

    public function template()
    {
        return "{{label}}<br><small><span style='color:gray' v-html='uri'></span></small>";
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