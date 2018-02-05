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
        return Page::orderBy("title")->get()->pluck("title", "id")->all();
    }

    /**
     * @return string|int
     */
    public function defaultValue()
    {
        return SharpGumSessionValue::get(
            "sidepanel_page",
            Page::orderBy("title")->first()->id
        );
    }
}