<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Sharp\EntityList\EntityListSelectRequiredFilter;

class DomainFilter implements EntityListSelectRequiredFilter
{

    public function label(): string
    {
        return "Domaine";
    }

    public function values(): array
    {
        return collect(config("gum.domains"))
            ->filter(function($label, $domain) {
                return gum_domain_allowed_to_user($domain);
            })
            ->all();
    }

    public function defaultValue()
    {
        return SharpGumSessionValue::getDomain();
    }

    public function isMaster(): bool
    {
        return true;
    }
}