<?php

namespace Code16\Gum\Models\Utils;

trait WithMenuTitle
{

    /**
     * @return string
     */
    public function getMenuTitleAttribute()
    {
        return $this->attributes["short_title"] ?: $this->attributes["title"];
    }
}