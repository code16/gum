<?php

namespace Code16\Gum\Sharp\Utils;

use Closure;
use Code16\Sharp\EntityList\SharpEntityList;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

abstract class GumSharpList extends SharpEntityList
{
    abstract protected function requestWiths(): array;

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    abstract protected function customTransformerFor(string $attribute);

    /**
     * Lookup for custom transformers fort each key and apply it.
     */
    protected function applyCustomTransformers()
    {
        $keys = $this->getDataKeys();
        if ($this->entityStateAttribute) {
            $keys[] = $this->entityStateAttribute;
        }

        foreach($keys as $key) {
            if($customTransformer = $this->customTransformerFor($key)) {
                $this->setCustomTransformer($key, $customTransformer);
            }
        }
    }
}