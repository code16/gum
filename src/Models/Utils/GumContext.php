<?php

namespace Code16\Gum\Models\Utils;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;

class GumContext
{
    protected static ContentUrl $leafContentUrl;
    protected static ?Section $section;

    public static function buildFor(array $segments)
    {
        self::$section = null;
        self::$leafContentUrl = ContentUrl::byPath(implode("/", $segments))->firstOrFail();
    }

    public static function theme(): ?string
    {
        return self::section()->style_key ?? null;
    }

    public static function section(): ?Section
    {
        if(is_null(self::$section)) {
            self::$section = self::findCurrentSection(self::$leafContentUrl);
        }

        return self::$section;
    }

    protected static function findCurrentSection(ContentUrl $contentUrl = null): ?Section
    {
        if(!$contentUrl) {
            return null;
        }

        if($contentUrl->content_type == Section::class) {
            self::$section = $contentUrl->content;

            return self::$section;
        }

        return $contentUrl->parent_id
            ? self::findCurrentSection($contentUrl->parent)
            : null;
    }
}