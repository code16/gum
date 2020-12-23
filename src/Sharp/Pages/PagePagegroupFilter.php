<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\EntityListSelectRequiredFilter;

class PagePagegroupFilter implements EntityListSelectRequiredFilter
{

    public function label(): string
    {
        return "Groupe";
    }

    public function values(): array
    {
        return ["0" => "- Aucun -"]
            + Pagegroup::orderBy("title")
                ->get()
                ->pluck("title", "id")
                ->all();
    }

    public function defaultValue()
    {
        return SharpGumSessionValue::get("page_pagegroup", "0");
    }
}