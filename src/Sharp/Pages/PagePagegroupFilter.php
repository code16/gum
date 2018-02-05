<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\EntityListRequiredFilter;

class PagePagegroupFilter implements EntityListRequiredFilter
{

    public function label()
    {
        return "Groupe";
    }

    /**
     * @return array
     */
    public function values()
    {
        return ["0" => "- Aucun -"]
            + Pagegroup::orderBy("title")
                ->get()
                ->pluck("title", "id")
                ->all();
    }

    /**
     * @return string|int
     */
    public function defaultValue()
    {
        return SharpGumSessionValue::get("page_pagegroup", "0");
    }
}