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
        return collect(config("gum.domains"))->filter(function($label, $domain) {
            return gum_domain_allowed_to_user($domain);
        })->all();
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