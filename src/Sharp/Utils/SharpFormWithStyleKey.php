<?php

namespace Code16\Gum\Sharp\Utils;

trait SharpFormWithStyleKey
{
    /**
     * @return bool
     */
    protected function hasStylesDefined(): bool
    {
        return $this->stylesDefined() && sizeof($this->stylesDefined());
    }

    /**
     * @return array
     */
    protected function stylesDefined()
    {
        return config(
            "gum.styles"
            . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() : "")
        );
    }
}