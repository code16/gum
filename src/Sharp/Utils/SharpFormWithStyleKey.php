<?php

namespace Code16\Gum\Sharp\Utils;

trait SharpFormWithStyleKey
{
    protected function hasStylesDefined(): bool
    {
        return $this->stylesDefined() && sizeof($this->stylesDefined());
    }

    protected function stylesDefined(): array
    {
        return config(
            "gum.styles"
            . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() : "")
        );
    }
}