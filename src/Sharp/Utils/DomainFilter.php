<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Sharp\EntityList\EntityListRequiredFilter;

class DomainFilter implements EntityListRequiredFilter
{

    public function label()
    {
        return "Domaine";
    }

    /**
     * @return array
     */
    public function values()
    {
        return config("gum.domains");
    }

    /**
     * @return string|int
     */
    public function defaultValue()
    {
        return SharpGumSessionValue::getDomain();
    }

    public function isMaster():bool
    {
        return true;
    }
}