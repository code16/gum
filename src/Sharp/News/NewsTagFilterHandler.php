<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\Tag;
use Code16\Sharp\EntityList\EntityListMultipleFilter;

class NewsTagFilterHandler implements EntityListMultipleFilter
{

    public function label()
    {
        return "Tag";
    }

    /**
     * @return array
     */
    public function values()
    {
        return Tag::orderBy("name")->pluck("name", "id");
    }

    public function isSearchable()
    {
        return true;
    }
}