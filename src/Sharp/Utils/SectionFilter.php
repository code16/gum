<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Gum\Models\Section;
use Code16\Sharp\EntityList\EntityListRequiredFilter;

class SectionFilter implements EntityListRequiredFilter
{

    public function label()
    {
        return "Section";
    }

    /**
     * @return array
     */
    public function values()
    {
        $sections = [];

        Section::domain(SharpGumSessionValue::getDomain())
            ->where("is_root", true)
            ->orderBy("root_menu_order")
            ->get()
            ->each(function(Section $rootSection) use(&$sections) {
                $sections[$rootSection->id] = $rootSection->url->uri;

                $sections += Section::whereExists(function($query) use($rootSection) {
                    return $query->from("content_urls")
                        ->where("domain", $rootSection->domain)
                        ->whereRaw("content_id = sections.id")
                        ->where("content_type", Section::class)
                        ->where("uri", "like", "{$rootSection->url->uri}/%");
                })
                    ->get()
                    ->pluck("url.uri", "id")
                    ->all();
            });

        return tap($sections, function(&$sections) {
            array_sort($sections);

            $sections += Section::domain(SharpGumSessionValue::getDomain())
                ->where("is_root", false)
                ->orderBy("title")
                ->whereNotExists(function($query) {
                    return $query->from("content_urls")
                        ->where("domain", SharpGumSessionValue::getDomain())
                        ->whereRaw("content_id = sections.id")
                        ->where("content_type", Section::class);
                })
                ->pluck("title", "id")
                ->map(function($title) {
                    return "~$title";
                })
                ->all();
        });
    }

    /**
     * @return string|int
     */
    public function defaultValue()
    {
        if($sectionId = SharpGumSessionValue::get("section")) {
            if(Section::domain(SharpGumSessionValue::getDomain())->find($sectionId)) {
                return $sectionId;
            }
        }

        return Section::domain(SharpGumSessionValue::getDomain())
            ->where("is_root", true)
            ->orderBy("root_menu_order")
            ->first()
            ->id;
    }
}