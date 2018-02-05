<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Sharp\EntityList\EntityListFilter;

class ContentWithUrlFilter implements EntityListFilter
{

    public function label()
    {
        return "Statut";
    }

    /**
     * @return array
     */
    public function values()
    {
        return [
            "off" => "Orphelines",
        ];
    }
}