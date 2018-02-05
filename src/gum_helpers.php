<?php

/**
 * @return string
 */
function gum_current_theme()
{
    return \Code16\Gum\Models\Utils\GumContext::theme();
}

/**
 * @param string $slug
 * @return string
 */
function append_suffix_to_slug(string $slug): string
{
    if(preg_match("/(.*?)-(\d+)$/", $slug, $matches)) {
        return sprintf("%s-%s", $matches[1], (int)$matches[2]+1);
    }

    return "$slug-2";
}