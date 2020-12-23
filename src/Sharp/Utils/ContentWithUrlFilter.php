<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Sharp\EntityList\EntityListSelectFilter;

class ContentWithUrlFilter implements EntityListSelectFilter
{

    public function label(): string
    {
        return "Statut";
    }

    public function values(): array
    {
        return [
            "off" => "Orphelines",
        ];
    }
}