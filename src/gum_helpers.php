<?php

function gum_domain_allowed_to_user(?string $domain, $user = null): bool
{
    $user = $user ?: auth()->user();

    if($domain && method_exists($user, "isAdminForDomain")) {
        return $user->isAdminforDomain($domain);
    }

    return true;
}

function gum_sharp_current_domain(): ?string
{
    if($resolver = config("gum.sharp_domain_resolver")) {
        return app($resolver)->resolveGumDomain();
    }
    
    return null;
}