<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Gum\Models\Section;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class UrlsCustomTransformer implements SharpAttributeTransformer
{

    /**
     * Transform a model attribute to array (json-able).
     *
     * @param $value
     * @param $instance
     * @param string $attribute
     * @return mixed
     */
    function apply($value, $instance = null, $attribute = null)
    {
        $urls = $instance instanceof Section
            ? ($instance->url ? collect([$instance->url]) : null)
            : $instance->urls;

        if(!$urls || $urls->isEmpty()) {
            return '<p class="mb-2" style="color:orange"><small>pas de lien</small></p>';
        }

        return '<p class="mb-2"><small>'
            . $urls->pluck("uri")->implode('</small></p><p class="mb-2"><small>')
            . '</small></p>';
    }
}