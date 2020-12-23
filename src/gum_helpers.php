<?php

function gum_current_theme(): ?string
{
    return \Code16\Gum\Models\Utils\GumContext::theme();
}

function append_suffix_to_slug(string $slug): string
{
    if(preg_match("/(.*?)-(\d+)$/", $slug, $matches)) {
        return sprintf("%s-%s", $matches[1], (int)$matches[2]+1);
    }

    return "$slug-2";
}

function gum_domain_allowed_to_user(?string $domain, $user = null): bool
{
    $user = $user ?: auth()->user();

    if($domain && method_exists($user, "isAdminForDomain")) {
        return $user->isAdminforDomain($domain);
    }

    return true;
}