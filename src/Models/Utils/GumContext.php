<?php

namespace Code16\Gum\Models\Utils;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;

class GumContext
{
    /** @var ContentUrl */
    protected static $leafContentUrl;

    /** @var Section */
    protected static $section;

    public static function buildFor(array $segments)
    {
        self::$section = null;
        self::$leafContentUrl = ContentUrl::byPath(implode("/", $segments))->firstOrFail();
    }

    /**
     * @return string|null
     */
    public static function theme()
    {
        return self::section()->style_key ?? null;
    }

    /**
     * @return Section|null
     */
    public static function section()
    {
        if(is_null(self::$section)) {
            self::$section = self::findCurrentSection(self::$leafContentUrl);
        }

        return self::$section;
    }

    /**
     * @param ContentUrl $contentUrl
     * @return Section|null
     */
    protected static function findCurrentSection(ContentUrl $contentUrl)
    {
        if($contentUrl->content_type == Section::class) {
            self::$section = $contentUrl->content;

            return self::$section;
        }

        return $contentUrl->parent_id
            ? self::findCurrentSection($contentUrl->parent)
            : null;
    }

}