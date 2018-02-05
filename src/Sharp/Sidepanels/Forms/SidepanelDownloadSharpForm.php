<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

class SidepanelDownloadSharpForm extends SidepanelSharpForm
{
    public function __construct()
    {
        $this->configure([
            "download"
        ]);
    }


    /**
     * @return string
     */
    protected function layoutKey(): string
    {
        return "download";
    }

    /**
     * @return string
     */
    protected function layoutLabel(): string
    {
        return "Téléchargement";
    }
}