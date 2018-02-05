<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

class SidepanelVisualSharpForm extends SidepanelSharpForm
{
    public function __construct()
    {
        $this->configure([
            "visual"
        ]);
    }


    /**
     * @return string
     */
    protected function layoutKey(): string
    {
        return "visual";
    }

    /**
     * @return string
     */
    protected function layoutLabel(): string
    {
        return "Visuel";
    }
}