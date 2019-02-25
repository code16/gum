<?php

namespace Code16\Gum\Search;

use Code16\Gum\Models\News;
use Code16\Gum\Models\Page;
use Illuminate\Pagination\Paginator;

class GumAlgoliaSearchEngine
{

    /**
     * @param string $query
     * @param string $domain
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function searchContent(string $query, string $domain, int $page = 1, int $perPage = 20)
    {
        $result = Page::search($query, function($algolia, $query, $options) use($domain, $page, $perPage) {
            return $algolia->search(
                $query, $options + [
                    'filters' => $domain,
                    'hitsPerPage' => $perPage,
                    'page' => $page - 1,
                    'highlightPreTag' => '<span class="highlighted">',
                    'highlightPostTag' => '</span>',
                    'attributesToSnippet' => ['text:30']
                ]
            );
        })->raw();

        return [
            new Paginator(
                collect($result["hits"] ?? [])
                    ->map(function($hit) use($domain) {
                        return (object)[
                            "title" => $hit["_highlightResult"]["title"]["value"],
                            "text" => $hit["_snippetResult"]["text"]["value"],
                            "group" => $hit["_highlightResult"]["group"]["value"],
                            "url" => $hit["type"] == "page" ? $hit["url"][$domain][0] : $hit["url"]
                        ];
                    }),
                $perPage-1,
                $page
            ),
            $result["nbHits"] ?? 0
        ];
    }

    /**
     * @param string $query
     * @param string $domain
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function searchNews(string $query, string $domain, int $page = 1, int $perPage = 20)
    {
        $result = News::search($query, function($algolia, $query, $options) use($domain, $page, $perPage) {
            return $algolia->search(
                $query, $options + [
                    'filters' => $domain,
                    'hitsPerPage' => $perPage,
                    'page' => $page - 1,
                    'highlightPreTag' => '<span class="highlighted">',
                    'highlightPostTag' => '</span>',
                    'attributesToSnippet' => ['text:30']
                ]
            );
        })->raw();

        return [
            new Paginator(
                collect($result["hits"] ?? [])
                    ->map(function($hit) use($domain) {
                        return (object)[
                            "id" => $hit["objectID"],
                            "title" => $hit["_highlightResult"]["title"]["value"],
                            "surtitle" => $hit["_highlightResult"]["surtitle"]["value"],
                            "published_at" => $hit["published_at"]["value"],
                            "text" => $hit["_snippetResult"]["text"]["value"],
                        ];
                    }),
                $perPage-1,
                $page
            ),
            $result["nbHits"] ?? 0
        ];
    }
}