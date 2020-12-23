<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\Tag;
use Code16\Sharp\EntityList\EntityListSelectMultipleFilter;

class NewsTagFilterHandler implements EntityListSelectMultipleFilter
{

    public function label(): string
    {
        return "Tag";
    }

    public function values(): array
    {
        return Tag::orderBy("name")
            ->pluck("name", "id")
            ->toArray();
    }

    public function isSearchable(): bool
    {
        return true;
    }
}