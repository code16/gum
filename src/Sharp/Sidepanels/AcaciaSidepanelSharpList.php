<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Sidepanel;
use Code16\Sharp\EntityList\Eloquent\Transformers\SharpUploadModelAttributeTransformer;

class AcaciaSidepanelSharpList extends SidepanelSharpList
{

    /**
     * @param string $layout
     * @param Sidepanel $sidepanel
     * @return string
     */
    protected function layoutCustomTransformer(string $layout, Sidepanel $sidepanel)
    {
        if($this->isVisual($sidepanel)) {
            return "Visuel";
        }

        if($this->isDownload($sidepanel)) {
            return "Téléchargement";
        }

        return "Contact";
    }

    /**
     * @param $content
     * @param Sidepanel $sidepanel
     * @return mixed
     * @throws \Code16\Sharp\Exceptions\SharpException
     */
    protected function contentCustomTransformer($content, Sidepanel $sidepanel)
    {
        if($this->isVisual($sidepanel) && $sidepanel->visual) {
            return sprintf('<div>%s<span>%s</span></div>',
                (new SharpUploadModelAttributeTransformer(100, 50))->apply(null, $sidepanel, "visual"),
                $sidepanel->visual->legend
            );
        }

        if($this->isDownload($sidepanel) && $sidepanel->downloadableFile) {
            return sprintf("%s, <em>%s</em>",
                basename($sidepanel->downloadableFile->mime_type),
                $sidepanel->downloadableFile->title
            );
        }

        return "";
    }

    /**
     * @param Sidepanel $sidepanel
     * @return bool
     */
    protected function isVisual(Sidepanel $sidepanel)
    {
        return $sidepanel->layout == "visual";
    }

    /**
     * @param Sidepanel $sidepanel
     * @return bool
     */
    protected function isDownload(Sidepanel $sidepanel)
    {
        return $sidepanel->layout == "download";
    }

}