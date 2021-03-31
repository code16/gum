<?php

use League\CommonMark\GithubFlavoredMarkdownConverter;

function gum_sharp_current_domain(): ?string
{
    if($resolver = config("gum.sharp_domain_resolver")) {
        return app($resolver)->resolveGumDomain();
    }
    
    return null;
}

function gum_markdown(?string $text): string
{
    return (new GithubFlavoredMarkdownConverter())->convertToHtml($text);
}